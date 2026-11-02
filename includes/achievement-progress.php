<?php
// Bottles and cups use the quantity checked by the human reviewer.
function achievement_progress($achievement_id, $user_id, $faculty_name = null) {
    $rules = rows('SELECT * FROM achievement_rules WHERE achievement_id=?',[$achievement_id]);
    $earned = count($rules)>0;
    foreach ($rules as &$rule) {
        $item = $rule['item'] === 'Approved activity' ? '' : $rule['item'];
        $measure = in_array($rule['item'],['Plastic bottles','Reusable cup'],true) ? 'COALESCE(SUM(s.quantity),0)' : 'COUNT(*)';
        $join = $faculty_name === null ? '' : ' JOIN users u ON u.id=s.user_id';
        $scope = $faculty_name === null ? 's.user_id=?' : "u.faculty=? AND u.role='user'";
        $sql="SELECT " . $measure . " AS total FROM submissions s JOIN challenges c ON c.id=s.challenge_id" . $join . " WHERE " . $scope . " AND s.status='approved'";
        $params=[$faculty_name ?? $user_id];
        if ($item !== '') { $sql.=' AND c.tracked_item=?'; $params[]=$item; }
        $rule['progress']=(int)one($sql,$params)['total'];
        $met=$rule['condition_name'] === 'Exactly' ? $rule['progress'] === (int)$rule['required_count'] : $rule['progress'] >= (int)$rule['required_count'];
        $earned=$earned && $met;
    }
    // Record individual qualification once; later requirement or quantity changes do not revoke it.
    if ($earned && $faculty_name === null && (int)$user_id > 0) {
        query('INSERT IGNORE INTO user_achievements (user_id,achievement_id) VALUES (?,?)',[$user_id,$achievement_id]);
    }
    // An individual award does not award a collective faculty target.
    $campaign_badge=$faculty_name === null ? one('SELECT * FROM user_achievements WHERE user_id=? AND achievement_id=?',[$user_id,$achievement_id]) : null;
    return ['earned'=>$earned || (bool)$campaign_badge,'rules'=>$rules];
}
