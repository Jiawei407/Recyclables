<?php
use Recyclables\Tests\Support\HttpTestCase;

/** The private test server must not claim uploads created by a separate schema/browser. */
final class UploadCleanupIsolationTest extends HttpTestCase
{
    private $unrelatedUpload;

    public function testCleanupLeavesAnUnreferencedConcurrentUploadAlone(): void
    {
        $folder = dirname(__DIR__, 2) . '/storage/uploads';
        if (!is_dir($folder)) mkdir($folder,0755,true);
        $this->unrelatedUpload = $folder . '/' . bin2hex(random_bytes(16)) . '.png';
        file_put_contents($this->unrelatedUpload, 'Owned by a different test process/schema');
        self::assertFileExists($this->unrelatedUpload);
    }

    protected function tearDown(): void
    {
        try {
            parent::tearDown();
            if ($this->unrelatedUpload) self::assertFileExists($this->unrelatedUpload,
                'Cleanup must only remove filenames referenced by its own disposable database.');
        } finally {
            if ($this->unrelatedUpload && is_file($this->unrelatedUpload)) unlink($this->unrelatedUpload);
        }
    }
}
