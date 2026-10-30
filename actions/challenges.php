<?php
if ($action === 'save-challenge') {
    require_role(['coordinator','admin']);
    $id = (int)post('id');
    $editing = $id > 0;
    $old = $id ? one('SELECT * FROM challenges WHERE id=?', [$id]) : null;
    need(!$id || $old, 'Select an existing challenge.');
    $title = post('challenge-title'); $description = post('challenge-description');
    need($title !== '' && strlen($title) <= 180 && $description !== '', 'Enter a title and description.');
    $category = post('challenge-category', $old['category'] ?? 'Recycling');
    need(in_array($category,['Cleanup','Recycling','Reuse','Everyday habits'],true), 'Choose a category.');
    $item = post('tracked-item',$old['tracked_item'] ?? 'Approved activity');
    need(in_array($item,['Plastic bottles','Reusable cup','Campus cleanup','Approved activity'],true), 'Choose the tracked item.');
    $points = number_value('challenge-points',0,1000);
    $start = date_value('challenge-start',$old['start_date'] ?? date('Y-m-d'));
    $end = date_value(isset($_POST['challenge-end-date']) ? 'challenge-end-date' : 'challenge-end');
    need($end >= $start, 'End date must be on or after the start date.');
    $limit = number_value('challenge-limit',1,1000,$old['submission_limit'] ?? '1');
    $eligibility = post('challenge-eligibility',$old['eligibility'] ?? 'Students and staff');
    need(in_array($eligibility,['Students and staff','Students','Staff'],true), 'Choose an eligibility rule.');
    $completion = post('challenge-completion',post('completion-requirement',$old['completion_requirement'] ?? ''));
    need(strlen($completion) <= 255, 'Completion requirement is too long.');
    $status = strtolower(post('challenge-status',$old['status'] ?? 'open'));
    need(in_array($status,['open','closed','draft'],true), 'Choose Open, Closed or Draft.');
    $image = save_upload('image') ?: ($old['image'] ?? '');
    mysqli_begin_transaction($conn);
    if ($id) {
        query('UPDATE challenges SET title=?,category=?,description=?,tracked_item=?,points=?,start_date=?,end_date=?,submission_limit=?,eligibility=?,completion_requirement=?,status=?,image=? WHERE id=?', [$title,$category,$description,$item,$points,$start,$end,$limit,$eligibility,$completion,$status,$image,$id]);
    } else {
        query('INSERT INTO challenges (title,category,description,tracked_item,points,start_date,end_date,submission_limit,eligibility,completion_requirement,status,image,created_by) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)', [$title,$category,$description,$item,$points,$start,$end,$limit,$eligibility,$completion,$status,$image,$user['id']]);
        $id = mysqli_insert_id($conn);
    }
    audit('challenge-saved','Challenge #' . $id . ': ' . $title);
    mysqli_commit($conn);
    action_success('Challenge', $editing ? 'updated' : 'created', $user['role'] === 'admin' ? 'admin/challenge-balancing.php?id=' . $id : 'coordinator/challenges-all-challenges.php', $title);
}
if ($action === 'delete-challenge') {
    require_role(['admin','coordinator']);
    $id = (int)post('id');
    need(one('SELECT id FROM challenges WHERE id=?',[$id]), 'Select a challenge.');
    need(!one('SELECT id FROM submissions WHERE challenge_id=? LIMIT 1',[$id]), 'This challenge has evidence. Close it instead to preserve its history.');
    mysqli_begin_transaction($conn);
    query('DELETE FROM challenges WHERE id=?',[$id]);
    audit('challenge-deleted','Challenge #' . $id);
    mysqli_commit($conn);
    action_success('Challenge', 'deleted', $user['role'] === 'admin' ? 'admin/challenge-balancing.php' : 'coordinator/challenges-all-challenges.php');
}
if ($action === 'join') {
    require_role('user');
    need((bool)$user['can_join'], 'Joining is disabled for your account.');
    $id = (int)post('id');
    $challenge = one("SELECT * FROM challenges WHERE id=? AND status='open' AND start_date<=CURDATE() AND end_date>=CURDATE()",[$id]);
    need($challenge, 'This challenge is not currently open.');
    need($challenge['eligibility'] !== 'Staff', 'This challenge is for staff. Registered student accounts cannot join it.');
    query('INSERT IGNORE INTO user_challenges (user_id,challenge_id) VALUES (?,?)',[$user['id'],$id]);
    action_success('Challenge', 'joined', 'user/challenges-my-challenges.php', $challenge['title'], 'You can now submit evidence.');
}
if ($action === 'submit-evidence') {
    require_role('user');
    need((bool)$user['can_submit'], 'Evidence submission is disabled for your account.');
    $id = (int)post('challenge-id'); $description = post('evidence-description');
    need($description !== '' && strlen($description) <= 5000, 'Describe what you did in up to 5000 characters.');
    mysqli_begin_transaction($conn);
    // Lock the membership so simultaneous requests cannot exceed the limit.
    need(one('SELECT * FROM user_challenges WHERE user_id=? AND challenge_id=? FOR UPDATE',[$user['id'],$id]), 'Join the challenge before submitting evidence.');
    $challenge = one("SELECT * FROM challenges WHERE id=? AND status='open' AND start_date<=CURDATE() AND end_date>=CURDATE()",[$id]);
    need($challenge, 'This challenge is no longer open.');
    $count = one("SELECT COUNT(*) AS total FROM submissions WHERE user_id=? AND challenge_id=? AND status IN ('pending','approved')",[$user['id'],$id]);
    need($count['total'] < $challenge['submission_limit'], 'You have reached the pending/approved submission limit.');
    $photo = save_upload('photo');
    need($photo !== '', 'Choose a photo for your evidence.');
    $hash = hash_file('sha256',__DIR__ . '/../storage/uploads/' . $photo);
    $quantity = number_value('quantity',1,1000000,'1');
    query('INSERT INTO submissions (user_id,challenge_id,description,quantity,photo,file_hash) VALUES (?,?,?,?,?,?)',[$user['id'],$id,$description,$quantity,$photo,$hash]);
    mysqli_commit($conn);
    action_success('Evidence', 'submitted', 'user/submit-evidence-submission-status.php', '', 'Your submission is waiting for manual review.');
}
if ($action === 'review') {
    require_role(['coordinator','admin']);
    $id = (int)post('id'); $decision = post('decision'); $notes = post('review-notes');
    mysqli_begin_transaction($conn);
    $submission = one('SELECT * FROM submissions WHERE id=? FOR UPDATE',[$id]);
    need($submission, 'Select an existing submission.');
    need(can_review($submission['status'],$decision), 'This submission is currently ' . str_replace('-', ' ', $submission['status']) . '. Only pending evidence can receive a new decision. Ask the participant to submit new evidence for another review.');
    need($notes !== '' || $decision === 'approved', 'Give a reason for rejection or revision.');
    if ($decision === 'approved') {
        need(isset($_POST['review-matches'],$_POST['review-count'],$_POST['review-duplicate']), 'Complete all three reviewer checks before approving.');
    }
    $challenge = one('SELECT * FROM challenges WHERE id=?',[$submission['challenge_id']]);
    $points = $decision === 'approved' ? (int)$challenge['points'] : 0;
    query('UPDATE submissions SET status=?,review_notes=?,reviewer_id=?,reviewed_at=NOW(),points_awarded=? WHERE id=?',[$decision,$notes,$user['id'],$points,$id]);
    if ($points > 0) {
        query('INSERT INTO point_transactions (user_id,submission_id,points,reason,actor_id) VALUES (?,?,?,?,?)',[$submission['user_id'],$id,$points,'Approved: ' . $challenge['title'],$user['id']]);
    }
    if ($decision === 'approved') {
        // Serialize campaign rewards for this account across different reviews.
        one('SELECT id FROM users WHERE id=? FOR UPDATE',[$submission['user_id']]);
        $campaigns=rows('SELECT ca.* FROM campaigns ca JOIN campaign_challenges cc ON cc.campaign_id=ca.id WHERE cc.challenge_id=? AND DATE(?) BETWEEN ca.start_date AND ca.end_date',[$submission['challenge_id'],$submission['created_at']]);
        foreach ($campaigns as $campaign) {
            $missing=one("SELECT COUNT(*) AS total FROM campaign_challenges cc WHERE cc.campaign_id=? AND NOT EXISTS(SELECT 1 FROM submissions s WHERE s.challenge_id=cc.challenge_id AND s.user_id=? AND s.status='approved' AND DATE(s.created_at) BETWEEN ? AND ?)",[$campaign['id'],$submission['user_id'],$campaign['start_date'],$campaign['end_date']]);
            if ($missing['total'] == 0 && !one('SELECT * FROM campaign_awards WHERE campaign_id=? AND user_id=?',[$campaign['id'],$submission['user_id']])) {
                query('INSERT INTO campaign_awards (campaign_id,user_id) VALUES (?,?)',[$campaign['id'],$submission['user_id']]);
                if ($campaign['bonus_points']>0) query('INSERT INTO point_transactions (user_id,points,reason,actor_id) VALUES (?,?,?,?)',[$submission['user_id'],$campaign['bonus_points'],'Campaign completed: ' . $campaign['name'],$user['id']]);
                if ($campaign['achievement_id']) query('INSERT IGNORE INTO user_achievements (user_id,achievement_id) VALUES (?,?)',[$submission['user_id'],$campaign['achievement_id']]);
            }
        }
    }
    audit('evidence-' . $decision,'Submission #' . $id . '; ' . $points . ' points. ' . $notes);
    mysqli_commit($conn);
    $destination = $user['role'] === 'admin' ? 'admin/home.php' : 'coordinator/submissions-' . $decision . '.php';
    if ($decision === 'needs-revision') action_success('Revision for submission #' . $id, 'requested', $destination);
    action_success('Submission #' . $id, $decision, $destination, '', $decision === 'approved' ? $points . ' Green Points awarded.' : '');
}
if ($action === 'save-campaign') {
    require_role('coordinator');
    $id = (int)post('id'); $name = post('campaign-name'); $description = post('campaign-description');
    $editing = $id > 0;
    need($name !== '' && strlen($name)<=180 && $description !== '', 'Enter a campaign name and description.');
    $target = number_value('campaign-target',1,1000000);
    $bonus = number_value('campaign-bonus',0,1000,'0');
    $start = date_value('campaign-start'); $end = date_value('campaign-end');
    need($end >= $start, 'Check the campaign dates.');
    $badge = (int)post('campaign-badge') ?: null;
    need(!$badge || one('SELECT id FROM achievements WHERE id=?',[$badge]), 'Choose an existing badge.');
    $challenge_ids = $_POST['campaign-challenges'] ?? [];
    need(is_array($challenge_ids) && count($challenge_ids)>0, 'Choose at least one campaign challenge.');
    foreach ($challenge_ids as $challenge_id) need(is_scalar($challenge_id) && ctype_digit((string)$challenge_id) && one('SELECT id FROM challenges WHERE id=?',[$challenge_id]), 'Choose existing challenges.');
    mysqli_begin_transaction($conn);
    if ($id) {
        need(!one('SELECT * FROM campaign_awards WHERE campaign_id=? LIMIT 1',[$id]), 'A campaign with awarded rewards cannot be changed. Create a new campaign instead.');
        need(one('SELECT id FROM campaigns WHERE id=?',[$id]), 'Select a campaign.');
        query('UPDATE campaigns SET name=?,description=?,participant_target=?,start_date=?,end_date=?,bonus_points=?,achievement_id=? WHERE id=?',[$name,$description,$target,$start,$end,$bonus,$badge,$id]);
    } else {
        query('INSERT INTO campaigns (name,description,participant_target,start_date,end_date,bonus_points,achievement_id,created_by) VALUES (?,?,?,?,?,?,?,?)',[$name,$description,$target,$start,$end,$bonus,$badge,$user['id']]);
        $id=mysqli_insert_id($conn);
    }
    query('DELETE FROM campaign_challenges WHERE campaign_id=?',[$id]);
    foreach (array_unique($challenge_ids) as $challenge_id) query('INSERT INTO campaign_challenges (campaign_id,challenge_id) VALUES (?,?)',[$id,$challenge_id]);
    mysqli_commit($conn);
    action_success('Campaign', $editing ? 'updated' : 'created', 'coordinator/campaigns-campaigns.php', $name);
}
if ($action === 'save-announcement') {
    require_role(['coordinator','faculty']);
    $id = (int)post('id'); $title = post('announcement-title'); $text = post('announcement-message');
    $editing = $id > 0;
    need($title !== '' && strlen($title)<=180 && $text !== '', 'Enter a title and message.');
    $date = date_value('announcement-date'); $audience = $user['role'] === 'faculty' ? 'My faculty' : post('announcement-audience','All users');
    $faculty = $user['role'] === 'faculty' ? $user['faculty'] : null;
    need(in_array($audience,['All users','Registered participants','Faculty representatives','Coordinators','My faculty','Faculty members'],true), 'Choose an audience.');
    if ($id) {
        $old = one('SELECT * FROM announcements WHERE id=?',[$id]);
        need($old && ($user['role'] === 'coordinator' || ($old['faculty'] === $user['faculty'] && $old['created_by'] === $user['id'])), 'You cannot edit this announcement.');
        query('UPDATE announcements SET title=?,message=?,publish_date=?,audience=?,faculty=? WHERE id=?',[$title,$text,$date,$audience,$faculty,$id]);
    } else {
        query('INSERT INTO announcements (title,message,publish_date,audience,faculty,created_by) VALUES (?,?,?,?,?,?)',[$title,$text,$date,$audience,$faculty,$user['id']]);
    }
    $operation = $editing ? 'updated' : ($date > date('Y-m-d') ? 'scheduled' : 'published');
    action_success('Announcement', $operation, $user['role'] . '/announcements-' . ($user['role'] === 'faculty' ? 'announcements' : 'all-announcements') . '.php', $title);
}
