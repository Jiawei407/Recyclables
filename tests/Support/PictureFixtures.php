<?php
namespace Recyclables\Tests\Support;

/** Small deterministic, valid PNGs, including full-resolution phone-sized fixtures. */
trait PictureFixtures
{
    private $fixtureFiles = [];

    protected function png(int $width = 1, int $height = 1, int $paddingBytes = 0): string
    {
        $file = tempnam(sys_get_temp_dir(), 'recyclables-photo-');
        $this->fixtureFiles[] = $file;
        $chunk = static function (string $type, string $bytes): string {
            return pack('N', strlen($bytes)) . $type . $bytes . pack('N', crc32($type . $bytes));
        };
        // Grayscale PNG rows, generated incrementally rather than allocating a decoded image.
        $deflate = deflate_init(ZLIB_ENCODING_DEFLATE);
        $compressed = '';
        for ($row = 0; $row < $height; $row++) {
            $compressed .= deflate_add($deflate, "\0" . str_repeat("\0", $width), ZLIB_NO_FLUSH);
        }
        $compressed .= deflate_add($deflate, '', ZLIB_FINISH);
        $bytes = "\x89PNG\r\n\x1a\n" . $chunk('IHDR', pack('NNCCCCC', $width, $height, 8, 0, 0, 0, 0));
        $bytes .= $chunk('IDAT', $compressed);
        if ($paddingBytes > 0) $bytes .= $chunk('tEXt', "Comment\0" . str_repeat('x', $paddingBytes));
        $bytes .= $chunk('IEND', '');
        file_put_contents($file, $bytes);
        return $file;
    }

    protected function deletePictureFixtures(): void
    {
        foreach ($this->fixtureFiles as $file) if (is_file($file)) unlink($file);
        $this->fixtureFiles = [];
    }
}
