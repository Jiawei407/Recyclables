<?php
use PHPUnit\Framework\TestCase;

/** Presentation must not change the application's request or stored-data contracts. */
final class RolePresentationTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        require_once dirname(__DIR__, 2) . '/includes/role-presentation.php';
    }

    private function actor(string $role = 'user'): array
    {
        return ['id' => 17, 'role' => $role, 'full_name' => 'Zoë & 李', 'faculty' => 'School of Computing'];
    }

    private function fixture(string $content = ''): string
    {
        return '<!doctype html><html lang="en" class="workspace-page"><head><meta charset="UTF-8"><title>Evidence — Recyclables</title><link rel="stylesheet" href="../styles.css"></head><body class="app-shell">'
            . '<a class="skip-link" href="#main-content">Skip to content</a>'
            . '<aside class="app-sidebar"><a class="brand" href="../index.php"><img src="../assets/brand-mark.svg" alt="">recyclables.</a>'
            . '<nav class="sidebar-nav" aria-label="Workspace navigation"><a href="../user/home.php">Home</a><a href="../user/submit-evidence-new-submission.php" aria-current="page">Submit Evidence</a></nav>'
            . '<div class="sidebar-bottom"><form method="post" action="/site/user/home.php"><input type="hidden" name="csrf" value="logout-csrf"><input type="hidden" name="action" value="logout"><button type="submit">Log out</button></form></div></aside>'
            . '<header class="workspace-header"><span>Evidence</span><span class="account-chip">Zoë &amp; 李</span></header>'
            . '<main id="main-content" class="workspace-content"><div class="page-header"><div><h1>Submit Evidence</h1><p class="context-line">Registered User</p><p class="page-description">Clear evidence. A considered review.</p></div></div>'
            . $content . '</main><script src="../scripts/app.js" defer></script></body></html>';
    }

    private function xpath(string $html): DOMXPath
    {
        $doc = new DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="UTF-8">' . $html);
        libxml_clear_errors(); libxml_use_internal_errors($previous);
        return new DOMXPath($doc);
    }

    /** @dataProvider excludedPages */
    public function testPublicAndInvalidRoutesAreByteIdentical(string $route): void
    {
        $html = $this->fixture('<p>Original content &amp; formatting</p>');
        self::assertSame($html, render_role_layout($html, $route, $this->actor()));
    }

    public static function excludedPages(): array
    {
        return [['index.php'], ['login.php'], ['register.php'], ['errors/404-page-not-found.php'],
            ['unknown/home.php'], ['user/../index.php'], ['../user/home.php'], ['user/home.php?role=admin']];
    }

    public function testRedirectOrEmptyOutputCannotAcquireLayoutMarkup(): void
    {
        self::assertSame('', render_role_layout('', 'user/home.php', $this->actor()));
        self::assertSame('Access denied.', render_role_layout('Access denied.', 'user/home.php', $this->actor()));
    }

    public function testNativeFieldsFormsPayloadsAndDraftTextSurvive(): void
    {
        $content = '<form id="backend-form" method="post" action="/site/user/submit-evidence-new-submission.php?id=5" enctype="multipart/form-data" hidden>'
            . '<input type="hidden" name="csrf" value="real-token-123"><input type="hidden" name="action" value="submit-evidence"><input type="hidden" name="id" value="5"></form>'
            . '<section class="panel"><div class="form-grid">'
            . '<label for="quantity">Quantity</label><input id="quantity" name="quantity" form="backend-form" type="number" min="1" max="9" step="2" value="3" required>'
            . '<label for="date">Date</label><input id="date" name="date" form="backend-form" type="date" min="2026-11-01" max="2026-11-30" step="2" value="2026-11-15">'
            . '<label for="faculty">Faculty</label><select id="faculty" name="faculty" form="backend-form" required><option value="">Choose</option><option value="School of Computing" selected>School of Computing</option><option value="Other &amp; more" disabled>Other &amp; more</option></select>'
            . '<label><input form="backend-form" type="radio" name="challenge-id" value="7" required checked>Challenge</label>'
            . '<label><input form="backend-form" type="checkbox" name="campaign-challenges[]" value="7" checked>Included</label>'
            . '<label for="upload">Evidence</label><input id="upload" form="backend-form" type="file" name="photo" accept="image/jpeg,image/png" required>'
            . '<label for="draft">Description</label><textarea id="draft" form="backend-form" name="description" rows="4">  &lt;b&gt;Draft &amp; café&lt;/b&gt;  </textarea>'
            . '<button type="submit" form="backend-form" name="decision" value="approved">Approve</button></div></section>';
        $before = $this->xpath($this->fixture($content));
        $after = $this->xpath(render_role_layout($this->fixture($content), 'user/submit-evidence-new-submission.php', $this->actor()));
        $contract = static function (DOMXPath $xpath): array {
            $result = [];
            foreach ($xpath->query('//main//input | //main//select | //main//textarea | //main//button | //main//form | //main//option') as $node) {
                $attrs = [];
                foreach (['id', 'name', 'type', 'form', 'method', 'action', 'enctype', 'value', 'min', 'max', 'step', 'required', 'accept', 'checked', 'selected', 'disabled', 'rows'] as $name) {
                    if ($node->hasAttribute($name)) $attrs[$name] = $node->getAttribute($name);
                }
                $result[] = [$node->tagName, $attrs, in_array($node->tagName, ['option', 'textarea', 'button'], true) ? $node->textContent : ''];
            }
            return $result;
        };
        $expected = $contract($before);
        // The approved photo policy expands this picker while retaining its name/form/validation contract.
        foreach ($expected as &$field) {
            if ($field[0] === 'input' && ($field[1]['name'] ?? '') === 'photo') {
                $field[1]['accept'] .= ',image/heic,image/heif,.heic,.heif';
            }
        }
        unset($field);
        self::assertSame($expected, $contract($after));
        self::assertSame(1, $after->query('//main//input[@name="csrf" and @value="real-token-123"]')->length);
        self::assertSame(0, $after->query('//main//form[@data-prototype-form]')->length);
    }

    public function testRealMediaLinksEscapedDataAndDialogArePreserved(): void
    {
        $content = '<article data-record><h2>People making progress</h2><p>A growing campus. A thoughtful review.</p><p>Zoë &amp; 李 &lt;script&gt;bad()&lt;/script&gt;</p></article>'
            . '<img id="evidence" src="../file.php?kind=submission&amp;id=99" alt="Participant evidence">'
            . '<a id="attachment" href="../file.php?kind=report&amp;id=3">Download attachment</a>';
        $html = $this->fixture($content);
        $html = str_replace('</body>', '<div class="dialog-overlay"><section class="action-dialog" role="dialog" aria-modal="true" aria-labelledby="dialog-title"><header class="dialog-header"><h2 id="dialog-title">Review evidence</h2><a class="close-button" href="../coordinator/submissions-pending.php">Close</a></header><div class="dialog-content"><input type="checkbox" name="photo-checked" form="review-form" required></div><form id="review-form" method="post"><input name="csrf" type="hidden" value="review-token"><button name="decision" value="approved" type="submit">Approve</button></form></section></div></body>', $html);
        $xpath = $this->xpath(render_role_layout($html, 'coordinator/submissions-review-submission.php', $this->actor('coordinator')));
        self::assertSame('People making progress', $xpath->query('//article[@data-record]/h2')->item(0)->textContent);
        self::assertSame('A growing campus. A thoughtful review.', $xpath->query('//article[@data-record]/p')->item(0)->textContent);
        self::assertStringContainsString('Zoë & 李 <script>bad()</script>', $xpath->query('//article[@data-record]')->item(0)->textContent);
        self::assertSame(0, $xpath->query('//article[@data-record]//script')->length);
        self::assertSame('../file.php?kind=submission&id=99', $xpath->query('//*[@id="evidence"]')->item(0)->getAttribute('src'));
        self::assertSame('../file.php?kind=report&id=3', $xpath->query('//*[@id="attachment"]')->item(0)->getAttribute('href'));
        self::assertSame(1, $xpath->query('//section[@role="dialog" and @aria-modal="true"]')->length);
        self::assertSame(1, $xpath->query('//form[@id="review-form"]//input[@name="csrf" and @value="review-token"]')->length);
        self::assertSame(1, $xpath->query('//input[@name="photo-checked" and @form="review-form" and @required]')->length);
    }

    public function testProductionShellHasNoPrototypeControlsOrFakeRecords(): void
    {
        $html = render_role_layout($this->fixture('<section class="panel"><h2>Actual records</h2></section>'), 'user/submit-evidence-new-submission.php', $this->actor());
        $xpath = $this->xpath($html);
        self::assertSame(1, $xpath->query('//html[contains(concat(" ",normalize-space(@class)," ")," clay-site ")]')->length);
        self::assertSame(1, $xpath->query('//header[contains(concat(" ",normalize-space(@class)," ")," clay-header ")]')->length);
        self::assertGreaterThan(0, $xpath->query('//a[@href="../user/submit-evidence-new-submission.php"]')->length);
        self::assertGreaterThan(0, $xpath->query('//form//input[@name="action" and @value="logout"]')->length);
        self::assertSame(0, $xpath->query('//*[@data-role-switch or @data-prototype-form]')->length);
        self::assertStringNotContainsString('Design prototype', $html);
        self::assertStringNotContainsString('sample data', $html);
        self::assertStringNotContainsString('.html', $html);
        self::assertStringNotContainsString('prototype-feedback', $html);
        self::assertStringContainsString('Select a challenge', $xpath->query('//main//*[contains(concat(" ",@class," ")," page-description ")]')->item(0)->textContent);
    }

    public function testOnlyRequestedHomepageShortcutsAreRemoved(): void
    {
        $coordinator = $this->fixture('<div class="stack"><section class="panel panel--forest grid-2" aria-labelledby="program-title"><div class="stack"><h2 id="program-title">A growing campus.<br>A thoughtful review.</h2><div><a href="../coordinator/submissions-pending.php">Open review queue</a></div></div></section><section><h2>Review records</h2><a href="../coordinator/submissions-review-submission.php?id=8">View</a></section></div>');
        $coordinator = str_replace('../user/submit-evidence-new-submission.php', '../coordinator/submissions-pending.php', $coordinator);
        $xpath = $this->xpath(render_role_layout($coordinator, 'coordinator/home.php', $this->actor('coordinator')));
        self::assertSame(0, $xpath->query('//main//a[normalize-space(.)="Open review queue"]')->length);
        self::assertGreaterThan(0, $xpath->query('//header//a[@href="../coordinator/submissions-pending.php"]')->length);
        self::assertSame(1, $xpath->query('//a[@href="../coordinator/submissions-review-submission.php?id=8"]')->length);
        self::assertSame('Program overview', $xpath->query('//*[@id="program-title"]')->item(0)->textContent);
        $admin = $this->fixture('<div class="status-banner"><h2>Good. Keep the campus growing.</h2><p>Platform status</p></div><section class="panel"><h2>Point corrections</h2><a href="../admin/leaderboard-point-corrections.php">Open the review list</a></section>');
        $xpath = $this->xpath(render_role_layout($admin, 'admin/home.php', $this->actor('admin')));
        self::assertSame(0, $xpath->query('//main//*[contains(concat(" ",@class," ")," status-banner ")]')->length);
        self::assertSame(1, $xpath->query('//a[@href="../admin/leaderboard-point-corrections.php"]')->length);
    }
}
