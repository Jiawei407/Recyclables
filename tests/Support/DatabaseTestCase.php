<?php

use PHPUnit\Framework\TestCase;

/** Database integration only: never connects to the application's default database. */
abstract class DatabaseTestCase extends TestCase
{
    private $savedGlobals = [];
    private $connection;
    private static $sequence = 0;

    protected function setUp(): void
    {
        global $page, $user, $id, $record, $records, $role, $uid, $faculty, $section, $error;
        $name = getenv('RECYCLABLES_TEST_DB_NAME');
        if ($name === false || $name === '') {
            $this->markTestSkipped('Set RECYCLABLES_TEST_DB_NAME to an initialized, empty recyclables_test_* database.');
        }
        if (!preg_match('/\Arecyclables_test_[a-zA-Z0-9_]+\z/D', $name)) {
            $this->fail('Refusing database access: the test database must be named recyclables_test_<suffix>.');
        }
        if (!extension_loaded('mysqli')) {
            $this->markTestSkipped('The mysqli extension is required for database integration tests.');
        }
        foreach (['conn', 'user', 'role', 'uid', 'faculty', 'section', 'record', 'page', 'id', 'records', 'error'] as $key) {
            $this->savedGlobals[$key] = [array_key_exists($key, $GLOBALS), $GLOBALS[$key] ?? null];
        }
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $this->connection = mysqli_connect(
            getenv('RECYCLABLES_TEST_DB_HOST') ?: '127.0.0.1',
            getenv('RECYCLABLES_TEST_DB_USER') ?: 'root',
            getenv('RECYCLABLES_TEST_DB_PASSWORD') ?: '',
            $name,
            (int) (getenv('RECYCLABLES_TEST_DB_PORT') ?: 3306)
        );
        mysqli_set_charset($this->connection, 'utf8mb4');
        $GLOBALS['conn'] = $this->connection;
        $this->assertSame($name, one('SELECT DATABASE() AS name')['name']);
        $this->assertSame(0, (int) one('SELECT COUNT(*) AS total FROM users')['total'],
            'Use an empty disposable test database without demo-account imports. Tests never clear existing data.');
        mysqli_begin_transaction($this->connection);
        $this->setContext(null);
        $GLOBALS['page'] = 'integration-test.php';
        // The dummy page has no selected-record query. Keep the real production functions.
        $savedGet = $_GET;
        $_GET = [];
        require_once dirname(__DIR__, 2) . '/includes/data.php';
        $_GET = $savedGet;
    }

    protected function tearDown(): void
    {
        if ($this->connection instanceof mysqli) {
            mysqli_rollback($this->connection);
            mysqli_close($this->connection);
        }
        foreach ($this->savedGlobals as $key => [$existed, $value]) {
            if ($existed) {
                $GLOBALS[$key] = $value;
            } else {
                unset($GLOBALS[$key]);
            }
        }
    }

    protected function setContext(?array $account, string $section = 'integration-test'): void
    {
        $GLOBALS['user'] = $account;
        $GLOBALS['role'] = $account['role'] ?? '';
        $GLOBALS['uid'] = $account['id'] ?? 0;
        $GLOBALS['faculty'] = $account['faculty'] ?? '';
        $GLOBALS['section'] = $section;
        $GLOBALS['record'] = [];
    }

    protected function account(array $changes = []): array
    {
        $n = ++self::$sequence;
        $data = array_merge([
            'full_name' => 'Test participant ' . $n,
            'email' => 'test' . $n . '@mail.apu.edu.my',
            'university_id' => 'TP' . str_pad((string) $n, 6, '0', STR_PAD_LEFT),
            'faculty' => 'School of Computing',
            'role' => 'user',
            'status' => 'active',
            'created_at' => '2025-01-01 00:00:00',
        ], $changes);
        query('INSERT INTO users (full_name,email,university_id,faculty,password,role,status,created_at) VALUES (?,?,?,?,?,?,?,?)', [
            $data['full_name'], $data['email'], $data['university_id'], $data['faculty'],
            password_hash('fixture-only!123', PASSWORD_DEFAULT), $data['role'], $data['status'], $data['created_at'],
        ]);
        return one('SELECT * FROM users WHERE id=?', [mysqli_insert_id($GLOBALS['conn'])]);
    }

    protected function challenge(array $changes = []): int
    {
        $data = array_merge([
            'title' => 'Test challenge', 'tracked_item' => 'Plastic bottles', 'status' => 'open',
            'start_date' => '2000-01-01', 'end_date' => '2099-12-31',
        ], $changes);
        query('INSERT INTO challenges (title,category,description,tracked_item,points,start_date,end_date,status) VALUES (?,?,?,?,?,?,?,?)', [
            $data['title'], 'Recycling', 'Integration fixture', $data['tracked_item'], 30,
            $data['start_date'], $data['end_date'], $data['status'],
        ]);
        return (int) mysqli_insert_id($GLOBALS['conn']);
    }

    protected function submission(int $accountId, int $challengeId, string $status = 'approved', int $quantity = 1, string $created = '2025-01-15 12:00:00'): int
    {
        query('INSERT INTO submissions (user_id,challenge_id,description,quantity,photo,file_hash,status,created_at) VALUES (?,?,?,?,?,?,?,?)', [
            $accountId, $challengeId, 'Verified fixture activity', $quantity, 'fixture.jpg',
            hash('sha256', 'fixture-' . ++self::$sequence), $status, $created,
        ]);
        return (int) mysqli_insert_id($GLOBALS['conn']);
    }

    protected function badge(array $requirements = []): int
    {
        query("INSERT INTO achievements (name,status) VALUES ('Test badge','active')");
        $id = (int) mysqli_insert_id($GLOBALS['conn']);
        foreach ($requirements as [$count, $item, $condition]) {
            query('INSERT INTO achievement_rules (achievement_id,required_count,item,condition_name) VALUES (?,?,?,?)', [$id, $count, $item, $condition]);
        }
        return $id;
    }

    protected function points(int $accountId, int $amount): void
    {
        query('INSERT INTO point_transactions (user_id,points,reason) VALUES (?,?,?)', [$accountId, $amount, 'Integration fixture']);
    }

    protected function ids(array $records): array
    {
        return array_map('intval', array_column($records, 'id'));
    }
}
