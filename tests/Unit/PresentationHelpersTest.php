<?php

use PHPUnit\Framework\TestCase;

final class PresentationHelpersTest extends TestCase
{
    private $originalPost;
    private $originalSession;
    private $sessionExisted;
    private $originalRecord;
    private $recordExisted;

    public static function setUpBeforeClass(): void
    {
        // A neutral page does not request a record or connect to MySQL.
        // Local include variables prevent the loader changing application globals.
        $get = $_GET;
        $_GET = [];
        $page = 'unit-test.php';
        $user = null;
        try {
            require_once dirname(__DIR__, 2) . '/includes/data.php';
        } finally {
            $_GET = $get;
        }
    }

    protected function setUp(): void
    {
        $this->originalPost = $_POST;
        $this->sessionExisted = array_key_exists('_SESSION', $GLOBALS);
        $this->originalSession = $_SESSION ?? null;
        $this->recordExisted = array_key_exists('record', $GLOBALS);
        $this->originalRecord = $GLOBALS['record'] ?? null;
        $_POST = [];
        $_SESSION = [];
        $GLOBALS['record'] = [];
    }

    protected function tearDown(): void
    {
        $_POST = $this->originalPost;
        if ($this->sessionExisted) {
            $_SESSION = $this->originalSession;
        } else {
            unset($GLOBALS['_SESSION']);
        }
        if ($this->recordExisted) {
            $GLOBALS['record'] = $this->originalRecord;
        } else {
            unset($GLOBALS['record']);
        }
    }

    /** @dataProvider escapedValues */
    public function testHtmlOutputEscapesTextAndBothKindsOfQuotes($input, string $expected): void
    {
        self::assertSame($expected, e($input));
    }

    public static function escapedValues(): array
    {
        return [
            'markup' => ['<script>alert("x")</script>', '&lt;script&gt;alert(&quot;x&quot;)&lt;/script&gt;'],
            'attribute breakout' => ['" autofocus onfocus="alert(1)', '&quot; autofocus onfocus=&quot;alert(1)'],
            'apostrophe' => ["O'Brien", 'O&#039;Brien'],
            'ampersand' => ['Reuse & recycle', 'Reuse &amp; recycle'],
            'unicode' => ['回收 ♻', '回收 ♻'],
            'ordinary text' => ['Campus cleanup', 'Campus cleanup'],
            'zero' => [0, '0'],
            'null' => [null, ''],
            'empty' => ['', ''],
        ];
    }

    public function testCsrfFieldContainsTheSessionToken(): void
    {
        $_SESSION['csrf'] = str_repeat('a1', 32);

        self::assertSame(
            '<input type="hidden" name="csrf" value="' . str_repeat('a1', 32) . '">',
            csrf_field()
        );
    }

    public function testCsrfFieldEscapesAttributeContent(): void
    {
        $_SESSION['csrf'] = '"><script>alert(1)</script>';

        self::assertSame(
            '<input type="hidden" name="csrf" value="&quot;&gt;&lt;script&gt;alert(1)&lt;/script&gt;">',
            csrf_field()
        );
    }

    public function testFailedFormSubmissionKeepsPostedTextInsteadOfStoredText(): void
    {
        $GLOBALS['record'] = ['full_name' => 'Stored name'];
        $_POST['full-name'] = 'Edited name';

        self::assertSame('Edited name', value('full-name'));
        self::assertSame('Stored name', $GLOBALS['record']['full_name']);
    }

    public function testPostedEmptyStringIsNotReplacedWithStoredText(): void
    {
        $GLOBALS['record'] = ['full_name' => 'Stored name'];
        $_POST['full-name'] = '';

        self::assertSame('', value('full-name'));
    }

    public function testPostedTextIsEscapedWithoutTrimmingTheUsersDraft(): void
    {
        $_POST['description'] = ' <b>Changed & reviewed</b> ';

        self::assertSame(' &lt;b&gt;Changed &amp; reviewed&lt;/b&gt; ', value('description'));
    }

    public function testRecordColumnCanBeExplicitOrDerivedFromDashedFieldName(): void
    {
        $GLOBALS['record'] = ['full_name' => "O'Brien", 'email' => 'tp090526@mail.apu.edu.my'];

        self::assertSame('O&#039;Brien', value('full-name'));
        self::assertSame('tp090526@mail.apu.edu.my', value('profile-email', 'email'));
    }

    public function testMissingRecordValueUsesAnEscapedDefault(): void
    {
        self::assertSame('Choose &amp; continue', value('title', 'title', 'Choose & continue'));
        self::assertSame('', value('missing'));
    }

    public function testStoredZeroIsKeptInsteadOfDefault(): void
    {
        $GLOBALS['record'] = ['points' => 0];

        self::assertSame('0', value('points', 'points', '10'));
    }

    /** @dataProvider nonStringDrafts */
    public function testNonStringPostedValuesCannotReplaceStoredDisplayValues($input): void
    {
        $_POST['title'] = $input;
        $GLOBALS['record'] = ['title' => 'Original title'];

        self::assertSame('Original title', value('title'));
    }

    public static function nonStringDrafts(): array
    {
        return [
            'array' => [['injected']],
            'nested array' => [['value' => ['injected']]],
            'null' => [null],
            'integer' => [123],
            'boolean' => [true],
        ];
    }

    public function testNullStoredValueUsesTheDefault(): void
    {
        $GLOBALS['record'] = ['title' => null];

        self::assertSame('Default', value('title', 'title', 'Default'));
    }

    public function testSelectedRecognizesStoredAndPostedValues(): void
    {
        $GLOBALS['record'] = ['faculty' => 'School of Computing'];
        self::assertSame(' selected', selected('faculty', 'faculty', 'School of Computing'));
        self::assertSame('', selected('faculty', 'faculty', 'School of Engineering'));

        $_POST['faculty'] = 'School of Engineering';
        self::assertSame(' selected', selected('faculty', 'faculty', 'School of Engineering'));
        self::assertSame('', selected('faculty', 'faculty', 'School of Computing'));
    }

    public function testSelectedComparesTheOriginalValueRatherThanHtmlEntities(): void
    {
        $_POST['choice'] = "Reuse & O'Brien";

        self::assertSame(' selected', selected('choice', 'choice', "Reuse & O'Brien"));
        self::assertSame('', selected('choice', 'choice', 'Reuse &amp; O&#039;Brien'));
    }

    public function testSelectedUsesTheDefaultAndMatchesNumericOptionsAsStrings(): void
    {
        self::assertSame(' selected', selected('status', 'status', 'draft', 'draft'));
        $GLOBALS['record'] = ['achievement_id' => 12];
        self::assertSame(' selected', selected('badge', 'achievement_id', 12));
        self::assertSame('', selected('badge', 'achievement_id', 13));
    }
}
