<?php
if ($action === 'register') {
    $name = post('full-name'); $email = strtolower(post('email'));
    $id = post('student-id'); $faculty = post('faculty');
    $password = raw_password('password'); $confirm = raw_password('confirm-password');
    $problem = account_error($name, $email, $id, $faculty, $password, $confirm);
    need($problem === '', $problem);
    query('INSERT INTO users (full_name,email,university_id,faculty,password,role) VALUES (?,?,?,?,?,?)', [$name,$email,$id,$faculty,password_hash($password, PASSWORD_DEFAULT),'user']);
    action_success('Account', 'created', 'login.php', '', 'You can now log in.');
}
if ($action === 'login') {
    $account = one('SELECT * FROM users WHERE email = ?', [strtolower(post('email'))]);
    need($account && password_verify(raw_password('password'), $account['password']) && $account['status'] === 'active', 'Email or password is incorrect, or this account is inactive.');
    session_regenerate_id(true);
    $_SESSION['user_id'] = $account['id'];
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
    redirect($account['role'] . '/home.php');
}
if ($action === 'logout') {
    $_SESSION = [];
    session_destroy();
    redirect('login.php');
}
if ($action === 'profile') {
    require_role(['user','faculty','coordinator','admin']);
    $name = post('profile-name', post('account-name'));
    $email = strtolower(post('profile-email', post('account-email')));
    $id = post('profile-id', post('account-id'));
    // A representative cannot move themselves into a different faculty.
    $faculty = $user['role'] === 'user' ? post('profile-faculty') : $user['faculty'];
    $problem = account_error($name,$email,$id,$faculty,'','',false,$user['role']);
    need($problem === '', $problem);
    query('UPDATE users SET full_name=?, email=?, university_id=?, faculty=? WHERE id=?', [$name,$email,$id,$faculty,$user['id']]);
    $destination = $user['role'] === 'user' ? 'user/profile-profile.php' : ($user['role'] === 'coordinator' ? 'coordinator/profile-manage-account.php' : $user['role'] . '/home.php');
    action_success('Account details', 'updated', $destination);
}
if ($action === 'password') {
    require_role(['user','faculty','coordinator','admin']);
    $current = raw_password(isset($_POST['password-current']) ? 'password-current' : 'current-password');
    $password = raw_password(isset($_POST['password-new']) ? 'password-new' : 'new-password');
    $confirm = raw_password(isset($_POST['password-confirm']) ? 'password-confirm' : 'confirm-password');
    need(password_verify($current, $user['password']), 'Your current password is incorrect.');
    $problem = new_password_error($password);
    need($problem === '', $problem);
    need($password === $confirm, 'The passwords do not match.');
    query('UPDATE users SET password=? WHERE id=?', [password_hash($password,PASSWORD_DEFAULT),$user['id']]);
    session_regenerate_id(true);
    $destination = $user['role'] === 'user' ? 'user/profile-edit-details.php' : ($user['role'] === 'coordinator' ? 'coordinator/profile-manage-account.php' : $user['role'] . '/home.php');
    action_success('Password', 'updated', $destination);
}
