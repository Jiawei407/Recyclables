<?php
use PHPUnit\Framework\TestCase;

final class UploadPolicyTest extends TestCase
{
    public function testUploadDefaultsSupportHighResolutionPhoneFixtures(): void
    {
        $bytes = getenv('RECYCLABLES_UPLOAD_MAX_BYTES'); $pixels = getenv('RECYCLABLES_UPLOAD_MAX_PIXELS');
        try {
            putenv('RECYCLABLES_UPLOAD_MAX_BYTES'); putenv('RECYCLABLES_UPLOAD_MAX_PIXELS');
            self::assertSame(256 * 1024 * 1024, upload_max_bytes());
            self::assertSame(256000000, upload_max_pixels());
            putenv('RECYCLABLES_UPLOAD_MAX_BYTES=123456'); putenv('RECYCLABLES_UPLOAD_MAX_PIXELS=48000000');
            self::assertSame(123456, upload_max_bytes()); self::assertSame(48000000, upload_max_pixels());
            putenv('RECYCLABLES_UPLOAD_MAX_BYTES=-1'); putenv('RECYCLABLES_UPLOAD_MAX_PIXELS=garbage');
            self::assertSame(256 * 1024 * 1024, upload_max_bytes()); self::assertSame(256000000, upload_max_pixels());
        } finally {
            putenv($bytes === false ? 'RECYCLABLES_UPLOAD_MAX_BYTES' : 'RECYCLABLES_UPLOAD_MAX_BYTES=' . $bytes);
            putenv($pixels === false ? 'RECYCLABLES_UPLOAD_MAX_PIXELS' : 'RECYCLABLES_UPLOAD_MAX_PIXELS=' . $pixels);
        }
    }

    private function box(string $type, string $payload): string { return pack('N', strlen($payload) + 8) . $type . $payload; }
    private function inspect(string $bytes) {
        $path = tempnam(sys_get_temp_dir(), 'heif-meta-'); file_put_contents($path, $bytes);
        try { return heif_dimensions($path); } finally { unlink($path); }
    }
    private function metadata(int $width, int $height): string {
        return $this->box('meta', pack('N', 0) . $this->box('iprp', $this->box('ipco', $this->box('ispe', pack('N3', 0, $width, $height)))));
    }
    public function testHeifMetadataRequiresDimensionsAndMediaWithinDeclaredBoxes(): void
    {
        // A structural metadata fixture, not a claim of HEVC pixel decoding.
        $ftyp = $this->box('ftyp', 'heic' . pack('N', 0) . 'mif1heic');
        $media = $this->box('mdat', 'payload');
        self::assertSame([8000,6000], $this->inspect($ftyp . $this->metadata(8000,6000) . $media));
        self::assertFalse($this->inspect($ftyp . $this->metadata(8000,6000)));
        self::assertFalse($this->inspect($ftyp . $media));
        self::assertFalse($this->inspect($ftyp . $this->metadata(0,6000) . $media));
        self::assertFalse($this->inspect($this->box('ftyp', 'xxxx' . pack('N', 0)) . $this->metadata(1,1) . $media));
        self::assertFalse($this->inspect($ftyp . $this->metadata(8000,6000) . pack('N', 10000) . 'mdatshort'));
    }
    public function testHeifUsesLargestImageInsteadOfThumbnailForPixelLimit(): void
    {
        $bytes = $this->box('ftyp', 'heic' . pack('N', 0)) . $this->metadata(320,240) . $this->metadata(12000,9000) . $this->box('mdat', 'data');
        self::assertSame([12000,9000], $this->inspect($bytes));
    }
}
