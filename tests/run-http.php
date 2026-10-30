<?php
// Starts a fresh PHP server with test-only DB configuration, runs HTTP tests, stops it.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$root = dirname(__DIR__);
$name = getenv('RECYCLABLES_TEST_DB_NAME') ?: '';
if (!preg_match('/^recyclables_test_[a-z0-9_]+$/D', $name)) {
    fwrite(STDERR, "Set RECYCLABLES_TEST_DB_NAME and run tests/prepare-database.php first.\n"); exit(1);
}
$phar = $root . '/tools/phpunit-9.phar';
if (!is_file($phar)) { fwrite(STDERR, "Download PHPUnit 9.6 into tools/phpunit-9.phar first.\n"); exit(1); }
$runtime = __DIR__ . '/.runtime';
if (!is_dir($runtime)) mkdir($runtime, 0700, true);
$env = getenv();
foreach (['HOST' => '127.0.0.1', 'PORT' => '3306', 'NAME' => $name, 'USER' => 'root', 'PASSWORD' => ''] as $key => $fallback) {
    $value = getenv('RECYCLABLES_TEST_DB_' . $key);
    $env['RECYCLABLES_DB_' . $key] = $value === false ? $fallback : $value;
}
// Bind a free local port rather than connecting to an existing user's website.
$socket = stream_socket_server('tcp://127.0.0.1:0', $errno, $error);
if (!$socket) { fwrite(STDERR, $error . "\n"); exit(1); }
$address = stream_socket_get_name($socket, false);
fclose($socket);
$env['RECYCLABLES_TEST_BASE_URL'] = 'http://' . $address;
$env['RECYCLABLES_TEST_SERVER_TOKEN'] = bin2hex(random_bytes(24));
$server = proc_open([PHP_BINARY, '-d', 'upload_max_filesize=256M', '-d', 'post_max_size=300M',
    '-d', 'session.save_path=' . $runtime, '-S', $address, '-t', $root, __DIR__ . '/Support/server-router.php'],
    [0 => ['pipe', 'r'], 1 => ['file', $runtime . '/server.log', 'a'], 2 => ['file', $runtime . '/server.log', 'a']], $pipes, $root, $env);
if (!is_resource($server)) { fwrite(STDERR, "Could not start the test server.\n"); exit(1); }
$exit = 1;
try {
    $ready = false;
    for ($i = 0; $i < 50; $i++) {
        $curl = curl_init($env['RECYCLABLES_TEST_BASE_URL'] . '/__test_health');
        curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_CONNECTTIMEOUT_MS => 200, CURLOPT_TIMEOUT_MS => 400]);
        $body = curl_exec($curl); curl_close($curl);
        $info = json_decode($body ?: '', true);
        if (($info['token'] ?? '') === $env['RECYCLABLES_TEST_SERVER_TOKEN'] && ($info['database'] ?? '') === $name) { $ready = true; break; }
        usleep(100000);
    }
    if (!$ready) throw new RuntimeException('Test server failed health check. See tests/.runtime/server.log.');
    $command = array_merge([PHP_BINARY, $phar, '-c', $root . '/phpunit.xml', '--testsuite', 'HTTP'], array_slice($argv, 1));
    $tests = proc_open($command, [0 => STDIN, 1 => STDOUT, 2 => STDERR], $testPipes, $root, $env);
    if (!is_resource($tests)) throw new RuntimeException('Could not start PHPUnit.');
    $exit = proc_close($tests);
} catch (Throwable $e) { fwrite(STDERR, $e->getMessage() . "\n"); }
finally { proc_terminate($server); proc_close($server); }
exit($exit);
