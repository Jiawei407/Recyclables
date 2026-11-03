<?php
use Recyclables\Tests\Support\HttpTestCase;

final class PagesTest extends HttpTestCase
{
    private const UPLOAD_ROUTES = [
        '/user/submit-evidence-upload-photo.php' => '/user/submit-evidence-new-submission.php',
        '/coordinator/challenges-create-upload-photo.php' => '/coordinator/challenges-create.php',
        '/coordinator/challenges-edit-upload-photo.php' => '/coordinator/challenges-edit.php',
        '/admin/achievements-create-upload-badge.php' => '/admin/achievements-create.php',
        '/admin/achievements-edit-upload-badge.php' => '/admin/achievements-edit.php',
        '/admin/challenge-balancing-upload-challenge-photo.php' => '/admin/challenge-balancing.php',
    ];
    public function testPublishedPageInventoryStillContainsAll93Screens(): void
    {
        $this->assertCount(93, self::pages(), 'Review route additions/removals explicitly rather than silently losing coverage.');
    }

    /** @dataProvider pages */
    public function testEachPageRendersUnderItsIntendedRole(string $path, string $role, int $status): void
    {
        if ($role !== '') $this->loginAs($this->user($role));
        $response = $this->request($path);
        // Production retains these legacy URLs as redirects to inline upload forms.
        if (isset(self::UPLOAD_ROUTES[$path])) {
            $this->assertSame(302, $response['status'], $path);
            $this->assertSame(self::UPLOAD_ROUTES[$path], $response['headers']['location'] ?? null);
            $response = $this->request(self::UPLOAD_ROUTES[$path]);
        }
        $this->assertSame($status, $response['status'], $path);
        $this->assertStringContainsString('<title>', $response['body'], $path);
        $this->assertStringContainsString('Recyclables', $response['body'], $path);
        foreach (['Fatal error', 'Warning:', 'Notice:', 'Uncaught ', 'mysqli_sql_exception'] as $diagnostic) {
            $this->assertStringNotContainsString($diagnostic, $response['body'], $path);
        }
        $document = new DOMDocument();
        $before = libxml_use_internal_errors(true);
        $document->loadHTML($response['body']);
        libxml_clear_errors(); libxml_use_internal_errors($before);
        $xpath = new DOMXPath($document);
        $this->assertGreaterThan(0, $xpath->query('//h1')->length, $path . ' needs a main heading.');
        foreach ($xpath->query('//form[translate(@method,"POST","post")="post"]') as $form) {
            $this->assertSame(1, $xpath->query('.//input[@name="csrf"]', $form)->length, $path . ': POST form needs CSRF.');
        }
    }

    public static function pages(): array
    {
        $cases = [];
        foreach (['index.php', 'login.php', 'register.php'] as $page) $cases[$page] = ['/' . $page, '', 200];
        foreach (['user', 'faculty', 'coordinator', 'admin'] as $role) {
            foreach (glob(dirname(__DIR__, 2) . '/' . $role . '/*.php') as $file) {
                $path = '/' . $role . '/' . basename($file);
                $cases[$path] = [$path, $role, 200];
            }
        }
        foreach (['401-sign-in-required' => 401, '403-access-denied' => 403, '404-page-not-found' => 404,
            '408-request-timeout' => 408, '500-server-error' => 500, '503-service-unavailable' => 503,
            'invalid-credentials' => 401, 'email-already-used' => 409] as $page => $status) {
            $cases[$page] = ['/errors/' . $page . '.php', '', $status];
        }
        return $cases;
    }
}
