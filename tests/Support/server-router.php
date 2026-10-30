<?php
// Used only by the test runner's private loopback server, never by XAMPP production.
if (PHP_SAPI !== 'cli-server' || !getenv('RECYCLABLES_TEST_SERVER_TOKEN')) { http_response_code(404); exit; }
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if ($path === '/__test_health') {
    require dirname(__DIR__, 2) . '/includes/config.php';
    $db = new mysqli($db_host, $db_user, $db_password, $db_name, $db_port);
    header('Content-Type: application/json');
    echo json_encode(['database' => $db->query('SELECT DATABASE() AS name')->fetch_assoc()['name'],
        'token' => getenv('RECYCLABLES_TEST_SERVER_TOKEN')]);
    exit;
}
if (preg_match('~^/(tests|tools|\.git)(/|$)~i', rawurldecode($path))) { http_response_code(403); exit; }
return require dirname(__DIR__, 2) . '/router.php';
