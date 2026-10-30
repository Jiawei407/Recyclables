<?php

use PHPUnit\Framework\TestCase;

/** Tests the production request helpers without starting a session or database. */
final class RequestAndValidationTest extends TestCase
{
    private $originalPost;

    protected function setUp(): void
    {
        $this->originalPost = $_POST;
        $_POST = [];
    }

    protected function tearDown(): void
    {
        $_POST = $this->originalPost;
    }

    /** @dataProvider textValues */
    public function testPostReturnsTrimmedStringsOrTheProvidedDefault($input, $expected): void
    {
        $_POST['title'] = $input;

        self::assertSame($expected, post('title', 'fallback'));
    }

    public static function textValues(): array
    {
        return [
            'ordinary title' => ['Recycle bottles', 'Recycle bottles'],
            'surrounding spaces' => [" \tCampus cleanup\r\n", 'Campus cleanup'],
            'empty remains empty' => ['', ''],
            'whitespace remains an empty string' => [" \r\n\t ", ''],
            'zero remains a string' => ['0', '0'],
            'internal line breaks remain' => ["First line\nSecond line", "First line\nSecond line"],
            'null is not a string' => [null, 'fallback'],
            'array injection is not accepted' => [['admin'], 'fallback'],
            'nested array injection is not accepted' => [['role' => ['admin']], 'fallback'],
            'integer is not a request string' => [123, 'fallback'],
            'boolean is not a request string' => [true, 'fallback'],
            'object is not a request string' => [(object) ['value' => 'admin'], 'fallback'],
        ];
    }

    public function testMissingPostValueUsesDefaultWithoutModifyingRequest(): void
    {
        $_POST = ['unrelated' => 'kept'];

        self::assertSame('', post('missing'));
        self::assertSame('default title', post('missing', 'default title'));
        self::assertSame(['unrelated' => 'kept'], $_POST);
    }

    /** @dataProvider passwordValues */
    public function testRawPasswordPreservesTheExactString($input, string $expected): void
    {
        $_POST['password'] = $input;

        self::assertSame($expected, raw_password('password'));
    }

    public static function passwordValues(): array
    {
        return [
            'spaces are significant' => ['  secret123!  ', '  secret123!  '],
            'tabs are significant' => ["\tsecret123!\t", "\tsecret123!\t"],
            'line break is not trimmed' => ["secret123!\n", "secret123!\n"],
            'unicode is retained' => ['密码12345!', '密码12345!'],
            'empty string' => ['', ''],
            'null' => [null, ''],
            'array injection' => [['secret123!'], ''],
            'integer injection' => [12345678, ''],
            'boolean injection' => [false, ''],
        ];
    }

    public function testMissingPasswordReturnsEmptyString(): void
    {
        self::assertSame('', raw_password('password'));
    }

    /** @dataProvider validNumbers */
    public function testNumberValueReturnsAnIntegerWithinItsInclusiveLimits(string $input, int $min, int $max, int $expected): void
    {
        $_POST['quantity'] = $input;

        self::assertSame($expected, number_value('quantity', $min, $max));
    }

    public static function validNumbers(): array
    {
        return [
            'zero reward allowed' => ['0', 0, 1000, 0],
            'minimum quantity' => ['1', 1, 1000000, 1],
            'ordinary quantity' => ['25', 1, 1000000, 25],
            'maximum reward' => ['1000', 0, 1000, 1000],
            'maximum quantity' => ['1000000', 1, 1000000, 1000000],
            'trimmed request input' => [' 25 ', 1, 1000000, 25],
            'leading zeros do not change quantity' => ['00025', 1, 1000000, 25],
            'single allowed value' => ['5', 5, 5, 5],
        ];
    }

    /** @dataProvider invalidNumbers */
    public function testNumberValueRejectsInvalidOrOutOfRangeInput($input): void
    {
        $_POST['quantity'] = $input;

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Check the value for quantity.');

        number_value('quantity', 1, 1000);
    }

    public static function invalidNumbers(): array
    {
        return [
            'below minimum' => ['0'],
            'above maximum' => ['1001'],
            'negative' => ['-1'],
            'explicit plus' => ['+1'],
            'fraction' => ['1.5'],
            'decimal integer notation' => ['1.0'],
            'scientific notation' => ['1e2'],
            'infinity' => ['INF'],
            'not a number' => ['NaN'],
            'huge integer' => [str_repeat('9', 100)],
            'empty' => [''],
            'whitespace' => ['  '],
            'internal whitespace' => ['1 0'],
            'letters' => ['ten'],
            'numeric suffix' => ['10kg'],
            'non-ASCII digits' => ['１２'],
            'null' => [null],
            'array injection' => [['10']],
            'integer instead of string' => [10],
            'float instead of string' => [1.5],
            'boolean instead of string' => [true],
        ];
    }

    public function testMissingNumberUsesItsValidDefault(): void
    {
        self::assertSame(1, number_value('quantity', 1, 1000, '1'));
        self::assertSame(0, number_value('points', 0, 1000, '0'));
    }

    public function testInvalidNumberDefaultIsAlsoValidated(): void
    {
        $this->expectException(Exception::class);

        number_value('quantity', 1, 1000, '1001');
    }

    /** @dataProvider validDates */
    public function testDateValueReturnsARealCanonicalCalendarDate(string $input, string $expected): void
    {
        $_POST['date'] = $input;

        self::assertSame($expected, date_value('date'));
    }

    public static function validDates(): array
    {
        return [
            'ordinary date' => ['2026-10-30', '2026-10-30'],
            'leap day' => ['2024-02-29', '2024-02-29'],
            'Gregorian leap century' => ['2000-02-29', '2000-02-29'],
            'year beginning' => ['2026-01-01', '2026-01-01'],
            'year end' => ['2026-12-31', '2026-12-31'],
            'surrounding spaces' => [' 2026-10-30 ', '2026-10-30'],
        ];
    }

    /** @dataProvider invalidDates */
    public function testDateValueRejectsInvalidOrNoncanonicalDates($input): void
    {
        $_POST['date'] = $input;

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Enter a valid date.');

        date_value('date');
    }

    public static function invalidDates(): array
    {
        return [
            'not a leap year' => ['2026-02-29'],
            'century is not a leap year' => ['1900-02-29'],
            'February overflow' => ['2024-02-30'],
            'April overflow' => ['2026-04-31'],
            'month zero' => ['2026-00-10'],
            'month overflow' => ['2026-13-10'],
            'day zero' => ['2026-10-00'],
            'day overflow' => ['2026-10-32'],
            'day-first format' => ['30-10-2026'],
            'slash separator' => ['2026/10/30'],
            'missing month padding' => ['2026-2-03'],
            'missing day padding' => ['2026-02-3'],
            'timestamp is not a date' => ['2026-10-30T12:00:00'],
            'relative date' => ['tomorrow'],
            'empty' => [''],
            'null' => [null],
            'array injection' => [['2026-10-30']],
        ];
    }

    public function testMissingDateUsesAndValidatesItsDefault(): void
    {
        self::assertSame('2026-10-30', date_value('date', '2026-10-30'));

        $this->expectException(Exception::class);
        date_value('date', '2026-02-29');
    }

    public function testNeedAllowsTrueConditions(): void
    {
        self::assertNull(need(true, 'Should not be raised.'));
    }

    public function testNeedRaisesTheMeaningfulValidationMessage(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Join the challenge before submitting evidence.');

        need(false, 'Join the challenge before submitting evidence.');
    }
}
