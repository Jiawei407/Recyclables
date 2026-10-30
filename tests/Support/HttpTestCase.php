<?php
namespace Recyclables\Tests\Support;

use PHPUnit\Framework\TestCase;

/** Real requests against the CLI test server and a dedicated disposable schema. */
abstract class HttpTestCase extends TestCase
{
    private $connection;
    private $previousConnection;
    private $cookie;
    private $token = '';
    private $base;
    private $originalFiles = [];
    private $cleanupEnabled = false;
    private static $sequence = 0;
    private const TABLES = ['audit_logs', 'faculty_reports', 'faculty_promotions', 'faculty_activities',
        'user_achievements', 'campaign_awards', 'campaign_challenges', 'announcements',
        'campaigns', 'achievement_rules', 'achievements', 'point_transactions', 'submissions',
        'user_challenges', 'challenges', 'users'];

    protected function setUp(): void
    {
        $this->base = getenv('RECYCLABLES_TEST_BASE_URL') ?: '';
        if ($this->base === '') $this->markTestSkipped('Use php tests/run-http.php to launch the isolated HTTP tests.');
        $name = getenv('RECYCLABLES_TEST_DB_NAME') ?: '';
        if (!getenv('RECYCLABLES_TEST_SERVER_TOKEN') || !preg_match('/^recyclables_test_[a-z0-9_]+$/D', $name) ||
            !preg_match('~^http://127\.0\.0\.1:[0-9]+$~D', $this->base)) {
            $this->fail('HTTP tests require a disposable test database and a loopback CLI test server.');
        }
        $this->cookie = tempnam(sys_get_temp_dir(), 'recyclables-test-');
        $health = $this->request('/__test_health');
        $this->assertSame(200, $health['status']);
        $info = json_decode($health['body'], true);
        $this->assertSame($name, $info['database'] ?? null, 'Wrong target database; no fixtures will be written.');
        $this->assertSame(getenv('RECYCLABLES_TEST_SERVER_TOKEN'), $info['token'] ?? null,
            'Only tests/run-http.php may launch the mutation tests.');
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $this->connection = new \mysqli(getenv('RECYCLABLES_TEST_DB_HOST') ?: '127.0.0.1',
            getenv('RECYCLABLES_TEST_DB_USER') ?: 'root', getenv('RECYCLABLES_TEST_DB_PASSWORD') ?: '',
            $name, (int)(getenv('RECYCLABLES_TEST_DB_PORT') ?: 3306));
        $this->connection->set_charset('utf8mb4');
        $marker = $this->connection->query('SELECT purpose FROM _test_harness')->fetch_assoc();
        $this->assertSame('recyclables-tests', $marker['purpose'] ?? null);
        $this->cleanupEnabled = true;
        $this->previousConnection = $GLOBALS['conn'] ?? null;
        $GLOBALS['conn'] = $this->connection;
        $this->clearFixtures();
        $this->originalFiles = glob(dirname(__DIR__, 2) . '/storage/uploads/*') ?: [];
    }

    protected function tearDown(): void
    {
        if ($this->connection && $this->cleanupEnabled) {
            $this->clearFixtures();
            $this->connection->close();
            $GLOBALS['conn'] = $this->previousConnection;
            // Only remove new upload files produced inside this test checkout.
            foreach (array_diff(glob(dirname(__DIR__, 2) . '/storage/uploads/*') ?: [], $this->originalFiles) as $file) {
                if (preg_match('/^[a-f0-9]{32}\.(jpg|png|pdf|heic|heif)$/D', basename($file)) && is_file($file)) unlink($file);
            }
        }
        if ($this->cookie && is_file($this->cookie)) unlink($this->cookie);
    }

    private function clearFixtures(): void
    {
        // DELETE in dependency order; foreign-key checks stay enabled.
        $this->connection->begin_transaction();
        foreach (self::TABLES as $table) $this->connection->query('DELETE FROM `' . $table . '`');
        $this->connection->commit();
    }

    protected function request(string $path, ?array $fields = null): array
    {
        $headers = [];
        $curl = curl_init($this->base . $path);
        curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_COOKIEFILE => $this->cookie, CURLOPT_COOKIEJAR => $this->cookie,
            CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_TIMEOUT => 15,
            CURLOPT_HEADERFUNCTION => static function ($curl, $line) use (&$headers) {
                $pair = explode(':', $line, 2);
                if (count($pair) === 2) $headers[strtolower(trim($pair[0]))] = trim($pair[1]);
                return strlen($line);
            }]);
        if ($fields !== null) {
            $multipart = (bool)array_filter($fields, static function ($v) { return $v instanceof \CURLFile; });
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $multipart ? $fields : http_build_query($fields));
        }
        $body = curl_exec($curl);
        if ($body === false) throw new \RuntimeException('HTTP test request failed: ' . curl_error($curl));
        $status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        curl_close($curl);
        if (preg_match('/name="csrf"[^>]*value="([^"]+)"/', $body, $match)) $this->token = html_entity_decode($match[1], ENT_QUOTES, 'UTF-8');
        return ['status' => $status, 'headers' => $headers, 'body' => $body];
    }

    protected function csrf(string $path = '/login.php'): string
    {
        $this->request($path);
        $this->assertNotSame('', $this->token, 'Expected a CSRF field on ' . $path);
        return $this->token;
    }

    protected function postAction(string $path, array $fields): array
    {
        if ($this->token === '') $this->csrf();
        return $this->request($path, array_merge(['csrf' => $this->token], $fields));
    }

    protected function loginAs(array $user): void
    {
        $this->token = '';
        $result = $this->postAction('/login.php', ['action' => 'login', 'email' => $user['email'], 'password' => $user['test_password']]);
        $this->assertSame(302, $result['status'], 'Fixture login failed: ' . $result['body']);
        $this->assertSame('/' . $user['role'] . '/home.php', $result['headers']['location'] ?? null);
        $this->token = ''; // Login regenerates CSRF and session IDs.
        $this->csrf('/' . $user['role'] . '/home.php');
    }

    protected function user(string $role = 'user', array $overrides = []): array
    {
        $n = ++self::$sequence;
        $u = array_merge(['full_name' => 'HTTP fixture ' . $n, 'email' => 'http' . $n . '@mail.apu.edu.my',
            'university_id' => 'TP' . str_pad((string)$n, 6, '0', STR_PAD_LEFT), 'faculty' => 'School of Computing',
            'status' => 'active', 'can_join' => 1, 'can_submit' => 1, 'created_at' => '2025-01-01 00:00:00',
            'test_password' => 'testing123@', 'role' => $role], $overrides);
        query('INSERT INTO users (full_name,email,university_id,faculty,password,role,status,can_join,can_submit,created_at) VALUES (?,?,?,?,?,?,?,?,?,?)',
            [$u['full_name'], $u['email'], $u['university_id'], $u['faculty'], password_hash($u['test_password'], PASSWORD_DEFAULT),
                $u['role'], $u['status'], $u['can_join'], $u['can_submit'], $u['created_at']]);
        return array_merge(one('SELECT * FROM users WHERE id=?', [mysqli_insert_id($this->connection)]), ['test_password' => $u['test_password']]);
    }

    protected function challenge(array $overrides = []): array
    {
        $c = array_merge(['title' => 'HTTP challenge', 'category' => 'Recycling', 'description' => 'Collect recyclables',
            'tracked_item' => 'Plastic bottles', 'points' => 30, 'start_date' => '2000-01-01', 'end_date' => '2099-12-31',
            'submission_limit' => 1, 'eligibility' => 'Students and staff', 'status' => 'open'], $overrides);
        query('INSERT INTO challenges (title,category,description,tracked_item,points,start_date,end_date,submission_limit,eligibility,status) VALUES (?,?,?,?,?,?,?,?,?,?)', array_values($c));
        return one('SELECT * FROM challenges WHERE id=?', [mysqli_insert_id($this->connection)]);
    }

    protected function membership(int $userId, int $challengeId): void
    {
        query('INSERT INTO user_challenges (user_id,challenge_id) VALUES (?,?)', [$userId, $challengeId]);
    }

    protected function submission(int $userId, int $challengeId, array $overrides = []): array
    {
        $s = array_merge(['description' => 'Evidence fixture', 'quantity' => 1, 'photo' => 'fixture.jpg',
            'file_hash' => hash('sha256', 'http-' . ++self::$sequence), 'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s')], $overrides);
        query('INSERT INTO submissions (user_id,challenge_id,description,quantity,photo,file_hash,status,created_at) VALUES (?,?,?,?,?,?,?,?)',
            [$userId, $challengeId, $s['description'], $s['quantity'], $s['photo'], $s['file_hash'], $s['status'], $s['created_at']]);
        return one('SELECT * FROM submissions WHERE id=?', [mysqli_insert_id($this->connection)]);
    }
}
