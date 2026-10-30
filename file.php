<?php
require __DIR__ . '/includes/bootstrap.php';
require_role(['user','faculty','coordinator','admin']);
$type = isset($_GET['type']) && is_string($_GET['type']) ? $_GET['type'] : '';
$id = (int)($_GET['id'] ?? 0);
$name='';
if ($type === 'evidence') {
    $submission=one('SELECT * FROM submissions WHERE id=?',[$id]);
    if ($submission && (in_array($user['role'],['coordinator','admin'],true) || (int)$submission['user_id'] === (int)$user['id'])) $name=$submission['photo'];
} elseif ($type === 'report') {
    $report=one('SELECT * FROM faculty_reports WHERE id=?',[$id]);
    if ($report && ($user['role'] === 'admin' || ($user['role'] === 'faculty' && $report['faculty'] === $user['faculty']))) $name=$report['attachment'];
} elseif ($type === 'challenge' || $type === 'achievement') {
    $table=$type === 'challenge' ? 'challenges' : 'achievements';
    $image=one('SELECT image FROM ' . $table . ' WHERE id=?',[$id]);
    $name=$image['image'] ?? '';
}
if (!preg_match('/^[a-f0-9]{32}\.(jpg|png|pdf)$/',$name) || !is_file(__DIR__ . '/storage/uploads/' . $name)) {
    http_response_code(404); exit('File not available.');
}
$path=__DIR__ . '/storage/uploads/' . $name;
$mime=(new finfo(FILEINFO_MIME_TYPE))->file($path);
header('Content-Type: ' . $mime);
header('Content-Security-Policy: default-src \'none\'; sandbox');
header('Content-Disposition: ' . ($mime === 'application/pdf' ? 'attachment' : 'inline') . '; filename="' . $name . '"');
readfile($path);
