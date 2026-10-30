<?php
// This include must run before any HTML is sent to the browser.
require_once __DIR__ . '/rules.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
date_default_timezone_set('Asia/Kuala_Lumpur');
session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off']);
session_start();
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');
// The base path also works if the project folder is renamed.
$base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
if (in_array(basename($base), ['user', 'faculty', 'coordinator', 'admin', 'errors'], true)) $base = str_replace('\\', '/', dirname($base));
if ($base === '/' || $base === '.') $base = '';
if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
$user = null;
$error = '';
$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $conn = mysqli_connect($db_host, $db_user, $db_password, $db_name, $db_port);
    mysqli_set_charset($conn, 'utf8mb4');
    if (isset($_SESSION['user_id'])) {
        $user = one('SELECT * FROM users WHERE id = ? AND status = ?', [$_SESSION['user_id'], 'active']);
        if (!$user) { unset($_SESSION['user_id']); }
    }
} catch (mysqli_sql_exception $exception) {
    error_log($exception->getMessage());
    http_response_code(503);
    exit('Database unavailable. Start MySQL in XAMPP, import database/recyclables_db.sql, and check includes/config.php.');
}
if (isset($page_role)) require_role([$page_role]);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!is_string($_POST['csrf'] ?? null) || !hash_equals($_SESSION['csrf'], $_POST['csrf'])) {
        http_response_code(403);
        exit('This form expired. Reload the page and try again.');
    }
    $new_uploads = [];
    try {
        $action = post('action');
        // Each file contains one understandable group of form handlers.
        require __DIR__ . '/../actions/accounts.php';
        require __DIR__ . '/../actions/challenges.php';
        require __DIR__ . '/../actions/faculty.php';
        require __DIR__ . '/../actions/admin.php';
        need(false, 'Unknown form action.');
    } catch (Exception $exception) {
        mysqli_rollback($conn);
        foreach ($new_uploads as $new_upload) { if (is_file($new_upload)) unlink($new_upload); }
        if ($exception instanceof mysqli_sql_exception) {
            error_log($exception->getMessage());
            $error = $exception->getCode() === 1062 ? 'This email, university ID or record is already in use.' : 'The change could not be saved. Check the database setup.';
        } else { $error = $exception->getMessage(); }
        http_response_code(422);
    }
}
