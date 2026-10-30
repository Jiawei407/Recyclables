<?php
// Read the selected record; never silently edit the first record in a table.
$id = isset($_GET['id']) && is_scalar($_GET['id']) ? max(0,(int)$_GET['id']) : 0;
$record = [];
$records = [];
$section = basename($page, '.php');
$role = $user['role'] ?? '';
$uid = $user['id'] ?? 0;
$faculty = $user['faculty'] ?? '';
if (str_contains($section,'challenges') || str_contains($section,'challenge-balancing') || str_contains($section,'participation')) {
    $record = $id ? (one('SELECT * FROM challenges WHERE id=?',[$id]) ?? []) : [];
} elseif (str_starts_with($section,'campaigns')) {
    $record = $id ? (one('SELECT * FROM campaigns WHERE id=?',[$id]) ?? []) : [];
} elseif (str_starts_with($section,'announcements')) {
    $record = $id ? (one('SELECT * FROM announcements WHERE id=?',[$id]) ?? []) : [];
    if ($record && $role === 'faculty' && $record['faculty'] !== $faculty) $record=[];
} elseif (str_starts_with($section,'users-') || $section === 'leaderboard-review-user-points') {
    $record = $id ? (one('SELECT * FROM users WHERE id=?',[$id]) ?? []) : [];
} elseif (str_starts_with($section,'profile-')) { $record=$user ?? [];
} elseif (str_starts_with($section,'achievements-')) {
    $record = $id ? (one('SELECT * FROM achievements WHERE id=?',[$id]) ?? []) : [];
} elseif (str_contains($section,'submission-details') || $section === 'submissions-review-submission') {
    $record = $id ? (one('SELECT s.*,u.full_name,c.title FROM submissions s JOIN users u ON u.id=s.user_id JOIN challenges c ON c.id=s.challenge_id WHERE s.id=?',[$id]) ?? []) : [];
    if ($record && $role === 'user' && (int)$record['user_id'] !== (int)$uid) $record=[];
} elseif (str_contains($section,'faculty-report') || str_contains($section,'activity-report')) {
    $record = $id ? (one('SELECT * FROM faculty_reports WHERE id=?',[$id]) ?? []) : [];
    if ($record && $role === 'faculty' && $record['faculty'] !== $faculty) $record=[];
} elseif ($section === 'faculty-activities-faculty-cleanup') {
    $record = $id ? (one('SELECT * FROM faculty_activities WHERE id=? AND faculty=?',[$id,$faculty]) ?? []) : [];
} elseif ($section === 'audit-log-audit-details') {
    $record = $id ? (one('SELECT a.*,u.full_name FROM audit_logs a LEFT JOIN users u ON u.id=a.actor_id WHERE a.id=?',[$id]) ?? []) : [];
}
if ($id && !$record) {
    http_response_code(404);
    $error = 'This record does not exist or is not available to your account.';
}
// Values for edit forms. POST values are retained if validation failed.
function value($field, $column = '', $default = '') {
    global $record;
    return e(isset($_POST[$field]) && is_string($_POST[$field]) ? $_POST[$field] : ($record[$column ?: str_replace('-','_',$field)] ?? $default));
}
function selected($field, $column, $option, $default = '') {
    return html_entity_decode(value($field,$column,$default),ENT_QUOTES,'UTF-8') === (string)$option ? ' selected' : '';
}
function page_records($kind) {
    global $user,$role,$uid,$faculty,$section,$record;
    if ($kind === 'users') return rows('SELECT u.*,COALESCE((SELECT SUM(p.points) FROM point_transactions p WHERE p.user_id=u.id),0) AS points FROM users u ORDER BY u.full_name');
    if ($kind === 'rankings') return rows("SELECT u.id,u.full_name,u.faculty,COALESCE(SUM(p.points),0) AS points FROM users u LEFT JOIN point_transactions p ON p.user_id=u.id WHERE u.role='user' AND u.status='active' GROUP BY u.id ORDER BY points DESC,u.full_name,u.id");
    if ($kind === 'faculty-rankings') return rows("SELECT u.faculty,COALESCE(SUM(p.points),0) AS points FROM users u LEFT JOIN point_transactions p ON p.user_id=u.id WHERE u.role='user' AND u.status='active' GROUP BY u.faculty ORDER BY points DESC,u.faculty");
    if ($kind === 'challenges') {
        $where = ''; $params=[];
        if ($role === 'user' || !$user) $where = " WHERE c.status='open' AND c.start_date<=CURDATE() AND c.end_date>=CURDATE()";
        if (str_contains($section,'my-challenges') || str_contains($section,'completed')) {
            $where = ' WHERE EXISTS(SELECT 1 FROM user_challenges uc WHERE uc.challenge_id=c.id AND uc.user_id=?)'; $params=[$uid];
            if (str_contains($section,'completed')) $where .= " AND EXISTS(SELECT 1 FROM submissions s WHERE s.challenge_id=c.id AND s.user_id=" . (int)$uid . " AND s.status='approved')";
        }
        if (str_contains($section,'featured-for-faculty')) { $where=" WHERE EXISTS(SELECT 1 FROM faculty_promotions fp WHERE fp.challenge_id=c.id AND fp.faculty=?)"; $params=[$faculty]; }
        if ($section === 'campaigns-performance' && $record) { $where=' WHERE EXISTS(SELECT 1 FROM campaign_challenges cc WHERE cc.challenge_id=c.id AND cc.campaign_id=?)'; $params=[$record['id']]; }
        $faculty_scope = $role === 'faculty' ? ' AND u.faculty=?' : '';
        $sql = "SELECT c.*, (SELECT COUNT(*) FROM user_challenges uc JOIN users u ON u.id=uc.user_id WHERE uc.challenge_id=c.id" . $faculty_scope . ") AS joined,
            (SELECT COUNT(DISTINCT s.user_id) FROM submissions s JOIN users u ON u.id=s.user_id WHERE s.challenge_id=c.id AND s.status='approved'" . $faculty_scope . ") AS completed FROM challenges c" . $where . ' ORDER BY c.id DESC';
        return rows($sql, array_merge($role === 'faculty' ? [$faculty,$faculty] : [],$params));
    }
    if ($kind === 'submissions') {
        $where=''; $params=[];
        if ($role === 'user') { $where=' WHERE s.user_id=?'; $params=[$uid]; }
        elseif ($role === 'faculty') { $where=' WHERE u.faculty=?'; $params=[$faculty]; }
        elseif (str_starts_with($section,'submissions-')) {
            $status=str_replace('submissions-','',$section);
            if ($status === 'review-submission') $status='pending';
            $where=' WHERE s.status=?'; $params=[$status];
        }
        return rows('SELECT s.*,u.full_name,c.title,(SELECT COUNT(*) FROM submissions s2 WHERE s2.file_hash=s.file_hash) AS duplicate_count FROM submissions s JOIN users u ON u.id=s.user_id JOIN challenges c ON c.id=s.challenge_id' . $where . ' ORDER BY s.id DESC',$params);
    }
    if ($kind === 'announcements') {
        if ($role === 'faculty' && str_starts_with($section,'announcements-')) return rows('SELECT * FROM announcements WHERE faculty=? ORDER BY publish_date DESC,id DESC',[$faculty]);
        if ($role === 'coordinator' && str_starts_with($section,'announcements-')) return rows('SELECT * FROM announcements ORDER BY publish_date DESC,id DESC');
        $audience=['user'=>'Registered participants','faculty'=>'Faculty representatives','coordinator'=>'Coordinators','admin'=>'All users'][$role] ?? 'All users';
        return rows("SELECT * FROM announcements WHERE publish_date<=CURDATE() AND (faculty IS NULL OR faculty=?) AND (audience IN ('All users','My faculty','Faculty members') OR audience=?) ORDER BY publish_date DESC,id DESC",[$faculty,$audience]);
    }
    if ($kind === 'campaigns') return rows('SELECT * FROM campaigns ORDER BY id DESC');
    if ($kind === 'activities') return rows('SELECT * FROM faculty_activities WHERE faculty=? ORDER BY activity_date DESC',[$faculty]);
    if ($kind === 'reports') return $role === 'admin' ? rows('SELECT * FROM faculty_reports ORDER BY id DESC') : rows('SELECT * FROM faculty_reports WHERE faculty=? ORDER BY id DESC',[$faculty]);
    if ($kind === 'audit') return rows('SELECT a.*,u.full_name FROM audit_logs a LEFT JOIN users u ON u.id=a.actor_id ORDER BY a.id DESC LIMIT 500');
    if ($kind === 'achievement-rules') return rows('SELECT ar.*,a.name FROM achievement_rules ar JOIN achievements a ON a.id=ar.achievement_id ORDER BY a.id,ar.id');
    if ($kind === 'transactions') return rows('SELECT * FROM point_transactions WHERE user_id=? ORDER BY id DESC',[$record['id'] ?? $uid]);
    if ($kind === 'actions-by-month') {
        $scope=$role === 'faculty' ? ' AND u.faculty=?' : ($role === 'user' ? ' AND u.id=?' : '');
        $params=$role === 'faculty' ? [$faculty] : ($role === 'user' ? [$uid] : []);
        return rows("SELECT DATE_FORMAT(s.created_at,'%Y-%m') AS period,COUNT(*) AS actions FROM submissions s JOIN users u ON u.id=s.user_id WHERE s.status='approved'" . $scope . ' GROUP BY period ORDER BY period DESC',$params);
    }
    if ($kind === 'participants-by-month') return rows("SELECT DATE_FORMAT(joined_at,'%Y-%m') AS period,COUNT(DISTINCT user_id) AS participants FROM user_challenges GROUP BY period ORDER BY period DESC");
    return [];
}
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
    // An individual campaign award does not award a collective faculty target.
    $campaign_badge=$faculty_name === null ? one('SELECT * FROM user_achievements WHERE user_id=? AND achievement_id=?',[$user_id,$achievement_id]) : null;
    return ['earned'=>$earned || (bool)$campaign_badge,'rules'=>$rules];
}
// Bottles and cups use the quantity checked by the human reviewer.
