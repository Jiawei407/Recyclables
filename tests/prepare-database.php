<?php
// Creates a NEW disposable schema. Never imports into or replaces a normal database.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$name = getenv('RECYCLABLES_TEST_DB_NAME') ?: '';
if (!preg_match('/^recyclables_test_[a-z0-9_]+$/D', $name)) {
    fwrite(STDERR, "Set RECYCLABLES_TEST_DB_NAME to recyclables_test_<unique suffix>.\n");
    exit(1);
}
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $conn = new mysqli(getenv('RECYCLABLES_TEST_DB_HOST') ?: '127.0.0.1',
        getenv('RECYCLABLES_TEST_DB_USER') ?: 'root', getenv('RECYCLABLES_TEST_DB_PASSWORD') ?: '',
        '', (int)(getenv('RECYCLABLES_TEST_DB_PORT') ?: 3306));
    // No IF NOT EXISTS: reusing an existing database is an error, not permission to reset it.
    $conn->query('CREATE DATABASE `' . $name . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    $conn->select_db($name);
    $sql = file_get_contents(dirname(__DIR__) . '/database/recyclables_db.sql');
    $sql = preg_replace('/^CREATE DATABASE[^;]*;\s*USE recyclables_ui_db;\s*/m', '', $sql, 1, $removed);
    if ($removed !== 1) throw new RuntimeException('Schema header changed. Review the importer before running.');
    $conn->multi_query($sql);
    do {
        if ($result = $conn->store_result()) $result->free();
    } while ($conn->more_results() && $conn->next_result());
    $conn->query("CREATE TABLE _test_harness (purpose VARCHAR(40) NOT NULL)");
    $conn->query("INSERT INTO _test_harness VALUES ('recyclables-tests')");
    echo "Prepared disposable test database: " . $name . "\n";
} catch (Throwable $e) {
    fwrite(STDERR, "Test database setup failed: " . $e->getMessage() . "\n");
    exit(1);
}
