<?php
require_once __DIR__ . '/achievement-progress.php';
function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
// Prepare the SQL first, then bind the values separately.
function query($sql, $values = []) {
    global $conn;
    $stmt = mysqli_prepare($conn, $sql);
    if ($values) mysqli_stmt_bind_param($stmt, str_repeat('s', count($values)), ...$values);
    mysqli_stmt_execute($stmt);
    return $stmt;
}
function rows($sql, $values = []) {
    return mysqli_fetch_all(mysqli_stmt_get_result(query($sql, $values)), MYSQLI_ASSOC);
}
function one($sql, $values = []) {
    $records = rows($sql, $values);
    return $records[0] ?? null;
}
function post($key, $default = '') {
    return isset($_POST[$key]) && is_string($_POST[$key]) ? trim($_POST[$key]) : $default;
}
function raw_password($key) {
    return isset($_POST[$key]) && is_string($_POST[$key]) ? $_POST[$key] : '';
}
function redirect($path, $status = 302) {
    global $base;
    header('Location: ' . $base . '/' . ltrim($path, '/'), true, $status);
    exit;
}
function csrf_field() {
    return '<input type="hidden" name="csrf" value="' . e($_SESSION['csrf']) . '">';
}
function require_role($roles) {
    global $user;
    if (!$user) redirect('login.php');
    if (!in_array($user['role'], (array)$roles, true)) {
        http_response_code(403);
        exit('Access denied. This function belongs to another role.');
    }
}
function need($condition, $message) {
    if (!$condition) throw new Exception($message);
}
function number_value($key, $min, $max, $default = '') {
    $value = post($key, $default);
    need(ctype_digit((string)$value) && (int)$value >= $min && (int)$value <= $max, 'Check the value for ' . $key . '.');
    return (int)$value;
}
function date_value($key, $default = '') {
    $date = post($key, $default);
    $parsed = DateTime::createFromFormat('!Y-m-d', $date);
    need($parsed && $parsed->format('Y-m-d') === $date, 'Enter a valid date.');
    return $date;
}
function audit($action, $details) {
    global $user;
    query('INSERT INTO audit_logs (actor_id, action, details) VALUES (?, ?, ?)', [$user['id'] ?? null, $action, $details]);
}
function upload_max_bytes() {
    $configured = getenv('RECYCLABLES_UPLOAD_MAX_BYTES');
    return $configured !== false && ctype_digit($configured) && (int)$configured > 0 ? (int)$configured : 256 * 1024 * 1024;
}
function upload_max_pixels() {
    $configured = getenv('RECYCLABLES_UPLOAD_MAX_PIXELS');
    return $configured !== false && ctype_digit($configured) && (int)$configured > 0 ? (int)$configured : 256000000;
}
/** Read bounded ISO-BMFF metadata; no image decoding or conversion is performed. */
function heif_dimensions($path) {
    $handle = fopen($path, 'rb');
    if (!$handle) return false;
    $length = filesize($path); $boxes = 0; $brand = false; $payload = false; $dimensions = false;
    $scan = function ($start, $end, $depth, $parent = '') use (&$scan, $handle, &$boxes, &$brand, &$payload, &$dimensions) {
        if ($depth > 8) return false;
        for ($offset = $start; $offset < $end;) {
            if ($end - $offset < 8 || ++$boxes > 10000) return false;
            fseek($handle, $offset); $header = fread($handle, 8);
            if (strlen($header) !== 8) return false;
            $size = unpack('N', substr($header, 0, 4))[1]; $type = substr($header, 4, 4); $headerSize = 8;
            if ($size === 1) {
                $extended = fread($handle, 8); if (strlen($extended) !== 8) return false;
                $parts = unpack('Nhigh/Nlow', $extended); if ($parts['high'] !== 0) return false;
                $size = $parts['low']; $headerSize = 16;
            } elseif ($size === 0) $size = $end - $offset;
            if ($size < $headerSize || $size > $end - $offset) return false;
            $content = $offset + $headerSize; $next = $offset + $size;
            if ($type === 'ftyp' && $depth === 0) {
                if ($size - $headerSize < 8 || $size - $headerSize > 4096) return false;
                fseek($handle, $content); $brands = fread($handle, $size - $headerSize);
                $candidates = array_merge([substr($brands, 0, 4)], str_split(substr($brands, 8), 4));
                $brand = (bool)array_intersect($candidates, ['heic','heix','hevc','hevx','mif1','msf1']);
            } elseif (($type === 'mdat' || ($type === 'idat' && $parent === 'meta')) && $size > $headerSize) $payload = true;
            elseif ($type === 'ispe' && $parent === 'ipco') {
                if ($size - $headerSize < 12) return false;
                fseek($handle, $content); $value = fread($handle, 12);
                $value = unpack('Nflags/Nwidth/Nheight', $value);
                if (!$value['width'] || !$value['height']) return false;
                // A container may have thumbnails; enforce the largest declared image size.
                if (!$dimensions || $value['width'] * $value['height'] > $dimensions[0] * $dimensions[1]) $dimensions = [$value['width'], $value['height']];
            } elseif (in_array($type, ['meta','iprp','ipco'], true)) {
                $children = $content + ($type === 'meta' ? 4 : 0);
                if ($children > $next || !$scan($children, $next, $depth + 1, $type)) return false;
            }
            $offset = $next;
        }
        return true;
    };
    try { return $scan(0, $length, 0) && $brand && $payload && $dimensions ? $dimensions : false; }
    finally { fclose($handle); }
}
function save_upload($field, $pdf_allowed = false) {
    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) return '';
    $file = $_FILES[$field];
    need($file['error'] === UPLOAD_ERR_OK && $file['size'] > 0 && $file['size'] <= upload_max_bytes(), 'Choose a file within the configured upload size limit.');
    $type = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $types = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/heic' => 'heic', 'image/heif' => 'heif'];
    if ($pdf_allowed) $types['application/pdf'] = 'pdf';
    need(isset($types[$type]), $pdf_allowed ? 'Choose a JPG, PNG, HEIC, HEIF or PDF.' : 'Choose a JPG, PNG, HEIC or HEIF image.');
    if ($type !== 'application/pdf') {
        $image = in_array($type, ['image/heic','image/heif'], true) ? heif_dimensions($file['tmp_name']) : @getimagesize($file['tmp_name']);
        need($image && $image[0] > 0 && $image[1] > 0 && $image[0] * $image[1] <= upload_max_pixels(), 'Choose a valid image within the configured pixel limit.');
    }
    $name = bin2hex(random_bytes(16)) . '.' . $types[$type];
    $folder = __DIR__ . '/../storage/uploads';
    if (!is_dir($folder)) mkdir($folder, 0755, true);
    need(move_uploaded_file($file['tmp_name'], $folder . '/' . $name), 'The file could not be saved.');
    $GLOBALS['new_uploads'][] = $folder . '/' . $name;
    return $name;
}
function success($message, $path) {
    $_SESSION['message'] = $message;
    // A successful POST returns to a normal GET page, closing any dialog.
    redirect($path, 303);
}
function action_success($entity, $operation, $path, $name = '', $details = '') {
    $label = $entity . ($name !== '' ? ' "' . $name . '"' : '');
    $message = $label . ' ' . $operation . ' successfully.';
    if ($details !== '') $message .= ' ' . $details;
    success($message, $path);
}
