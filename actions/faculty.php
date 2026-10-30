<?php
if ($action === 'save-activity') {
    require_role('faculty');
    $name = post('activity-name'); $location = post('activity-location'); $details = post('activity-details');
    $date = post('activity-date'); $parsed = DateTime::createFromFormat('!Y-m-d\TH:i',$date);
    need($parsed && $parsed->format('Y-m-d\TH:i') === $date, 'Enter a valid activity date and time.');
    need($name !== '' && strlen($name)<=180 && $location !== '' && strlen($location)<=180 && $details !== '', 'Complete the activity details.');
    $limit = number_value('activity-limit',1,1000000);
    query('INSERT INTO faculty_activities (name,faculty,activity_date,location,participant_limit,details,created_by) VALUES (?,?,?,?,?,?,?)',[$name,$user['faculty'],$parsed->format('Y-m-d H:i:s'),$location,$limit,$details,$user['id']]);
    action_success('Faculty activity', 'created', 'faculty/faculty-activities-proposed-activities.php', $name);
}
if ($action === 'promote') {
    require_role('faculty');
    $id = (int)post('id');
    need(one("SELECT id FROM challenges WHERE id=? AND status='open'",[$id]), 'Select an open challenge.');
    if (post('remove') === '1') {
        query('DELETE FROM faculty_promotions WHERE faculty=? AND challenge_id=?',[$user['faculty'],$id]);
    } else {
        query('INSERT IGNORE INTO faculty_promotions (faculty,challenge_id,created_by) VALUES (?,?,?)',[$user['faculty'],$id,$user['id']]);
    }
    action_success('Faculty promotion', 'updated', 'faculty/promote-challenges-featured-for-faculty.php');
}
if ($action === 'save-report') {
    require_role('faculty');
    $activity = post('report-activity'); $period = post('report-period'); $outcome = post('report-outcome');
    need($activity !== '' && strlen($activity)<=180 && $outcome !== '', 'Enter an activity and outcome.');
    need(in_array($period,['All time','Current month','Previous month'],true), 'Choose a reporting period.');
    $participants = number_value('report-participants',0,1000000);
    $attachment = save_upload('report-attachment',true);
    query('INSERT INTO faculty_reports (activity,period,participants,outcome,attachment,faculty,created_by) VALUES (?,?,?,?,?,?,?)',[$activity,$period,$participants,$outcome,$attachment,$user['faculty'],$user['id']]);
    action_success('Faculty report', 'submitted', 'faculty/submit-report-reports.php', $activity);
}
