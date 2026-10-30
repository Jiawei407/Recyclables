<?php
if ($action === 'save-user') {
    require_role('admin');
    $id = (int)post('id');
    $editing = $id > 0;
    $name = post('full-name'); $email = strtolower(post('email')); $university_id = post('university-id');
    $faculty = post('faculty'); $role = post('designated-role'); $status = strtolower(post('account-status','active'));
    $password = raw_password('new-password'); $confirm = raw_password('confirm-password');
    $problem = account_error($name,$email,$university_id,$faculty,$password,$confirm,!$id,$role);
    need($problem === '',$problem);
    need(valid_role($role) && in_array($status,['active','suspended','inactive'],true), 'Choose a role and account status.');
    need(!$id || one('SELECT id FROM users WHERE id=?',[$id]), 'Select a user.');
    need($id !== (int)$user['id'] || ($role === 'admin' && $status === 'active'), 'Keep your own administrator account active.');
    $join = isset($_POST['can-join']) ? 1 : 0; $submit = isset($_POST['can-submit']) ? 1 : 0;
    mysqli_begin_transaction($conn);
    if ($id) {
        query('UPDATE users SET full_name=?,email=?,university_id=?,faculty=?,role=?,status=?,can_join=?,can_submit=? WHERE id=?',[$name,$email,$university_id,$faculty,$role,$status,$join,$submit,$id]);
        if ($password !== '') query('UPDATE users SET password=? WHERE id=?',[password_hash($password,PASSWORD_DEFAULT),$id]);
    } else {
        query('INSERT INTO users (full_name,email,university_id,faculty,role,status,password,can_join,can_submit) VALUES (?,?,?,?,?,?,?,?,?)',[$name,$email,$university_id,$faculty,$role,$status,password_hash($password,PASSWORD_DEFAULT),$join,$submit]);
        $id = mysqli_insert_id($conn);
    }
    audit('user-saved','User #' . $id . '; role=' . $role . '; status=' . $status);
    mysqli_commit($conn);
    action_success('User', $editing ? 'updated' : 'created', 'admin/users-users.php', $name);
}
if (in_array($action,['delete-user','suspend-user'],true)) {
    require_role('admin'); $id = (int)post('id');
    need($id && $id !== (int)$user['id'], 'You cannot delete or suspend your own account.');
    need(one('SELECT id FROM users WHERE id=?',[$id]), 'Select a user.');
    mysqli_begin_transaction($conn);
    if ($action === 'delete-user') {
        need(!one('SELECT id FROM submissions WHERE user_id=? LIMIT 1',[$id]) && !one('SELECT id FROM point_transactions WHERE user_id=? LIMIT 1',[$id]), 'This user has evidence or points. Suspend the account to preserve the records.');
        query('DELETE FROM users WHERE id=?',[$id]);
    } else { query("UPDATE users SET status='suspended' WHERE id=?",[$id]); }
    audit($action,'User #' . $id);
    mysqli_commit($conn);
    action_success('Account', $action === 'delete-user' ? 'deleted' : 'suspended', 'admin/users-users.php');
}
if ($action === 'correct-points') {
    require_role('admin'); $id = (int)post('id'); $reason = post('review-reason');
    $decision = post('decision','deduct');
    need(in_array($decision,['deduct','approve','deny'],true) && $reason !== '' && strlen($reason)<=200, 'Enter a reason of up to 200 characters.');
    mysqli_begin_transaction($conn);
    need(one('SELECT id FROM users WHERE id=? FOR UPDATE',[$id]), 'Select a user.');
    $points = 0;
    if ($decision === 'deduct') {
        $points = number_value('points-to-deduct',1,1000000);
        $balance = one('SELECT COALESCE(SUM(points),0) AS total FROM point_transactions WHERE user_id=?',[$id]);
        need($points <= $balance['total'], 'A deduction cannot exceed the user balance.');
        query('INSERT INTO point_transactions (user_id,points,reason,actor_id) VALUES (?,?,?,?)',[$id,-$points,$reason,$user['id']]);
    }
    audit('points-' . $decision,'User #' . $id . '; deducted=' . $points . '; ' . $reason);
    mysqli_commit($conn);
    action_success($decision === 'deduct' ? 'Point deduction' : 'Point review', $decision === 'deduct' ? 'recorded' : ($decision === 'approve' ? 'approved' : 'denied'), 'admin/leaderboard-point-corrections.php');
}
if ($action === 'save-achievement') {
    require_role('admin'); $id = (int)post('id'); $name = post('achievement-name');
    $editing = $id > 0;
    $status = strtolower(post('achievement-status','draft'));
    need($name !== '' && strlen($name)<=120, 'Enter an achievement name of up to 120 characters.');
    need(in_array($status,['draft','active','archived'],true), 'Choose a valid achievement status.');
    $old = $id ? one('SELECT * FROM achievements WHERE id=?',[$id]) : null;
    need(!$id || $old, 'Select an achievement.');
    $rules = [];
    for ($i=1; $i<=3; $i++) {
        if (isset($_POST['exclude-' . $i])) continue;
        $count = post('requirement-' . $i . '-count'); $item = post('requirement-' . $i . '-item');
        $condition = post('requirement-' . $i . '-condition');
        if ($count === '' && $item === '' && $condition === '') continue;
        need(ctype_digit($count) && (int)$count > 0 && (int)$count <=1000000, 'Each requirement needs a positive count.');
        need(in_array($item,['Plastic bottles','Approved activity','Reusable cup','Campus cleanup'],true) && in_array($condition,['At least','Exactly'],true), 'Complete each requirement.');
        $rules[] = [(int)$count,$item,$condition];
    }
    need($status !== 'active' || count($rules)>0, 'An active achievement needs at least one requirement.');
    $image = save_upload('image') ?: ($old['image'] ?? '');
    mysqli_begin_transaction($conn);
    if ($id) { query('UPDATE achievements SET name=?,status=?,image=? WHERE id=?',[$name,$status,$image,$id]); }
    else { query('INSERT INTO achievements (name,status,image) VALUES (?,?,?)',[$name,$status,$image]); $id = mysqli_insert_id($conn); }
    query('DELETE FROM achievement_rules WHERE achievement_id=?',[$id]);
    foreach ($rules as $rule) query('INSERT INTO achievement_rules (achievement_id,required_count,item,condition_name) VALUES (?,?,?,?)',array_merge([$id],$rule));
    audit('achievement-saved','Achievement #' . $id);
    mysqli_commit($conn);
    action_success('Achievement', $editing ? 'updated' : 'created', 'admin/achievements-achievements.php', $name);
}
