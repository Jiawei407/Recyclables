<?php
use Recyclables\Tests\Support\HttpTestCase;
use Recyclables\Tests\Support\PictureFixtures;

final class UploadsAndAgreedRulesTest extends HttpTestCase
{
    use PictureFixtures;

    protected function tearDown(): void
    {
        $this->deletePictureFixtures();
        parent::tearDown();
    }

    private function evidence(array $fields = []): array
    {
        $participant = $this->user();
        $challenge = $this->challenge();
        $this->membership((int)$participant['id'], (int)$challenge['id']);
        $this->loginAs($participant);
        return $this->postAction('/user/submit-evidence-new-submission.php', array_merge([
            'action' => 'submit-evidence', 'challenge-id' => $challenge['id'],
            'evidence-description' => 'Collected bottles for recycling', 'quantity' => '5',
        ], $fields));
    }

    public function testValidPngCreatesPendingEvidenceWithoutPointsAndAllowsOnlyOwnerToReadFile(): void
    {
        $file = $this->png();
        $response = $this->evidence(['photo' => new CURLFile($file, 'image/png', 'evidence.png')]);
        $this->assertSame(303, $response['status']);
        $saved = one('SELECT * FROM submissions');
        $this->assertSame('pending', $saved['status']);
        $this->assertSame(5, (int)$saved['quantity']);
        $this->assertSame(hash_file('sha256', $file), $saved['file_hash']);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{32}\.png$/D', $saved['photo']);
        $this->assertSame(0, (int)one('SELECT COUNT(*) AS n FROM point_transactions')['n']);
        $read = $this->request('/file.php?type=evidence&id=' . $saved['id']);
        $this->assertSame(200, $read['status']);
        $this->assertSame('image/png', $read['headers']['content-type']);
        $this->assertSame(file_get_contents($file), $read['body']);
        $this->loginAs($this->user());
        $this->assertSame(404, $this->request('/file.php?type=evidence&id=' . $saved['id'])['status']);
    }

    public function testMissingPhotoCreatesNoSubmission(): void
    {
        $this->assertSame(422, $this->evidence()['status']);
        $this->assertSame(0, (int)one('SELECT COUNT(*) AS n FROM submissions')['n']);
    }

    public function testExecutableRenamedAsPngIsRejected(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'recyclables-unsafe-');
        try {
            file_put_contents($file, '<?php echo "unsafe";');
            $response = $this->evidence(['photo' => new CURLFile($file, 'image/png', 'photo.png')]);
            $this->assertSame(422, $response['status']);
            $this->assertSame(0, (int)one('SELECT COUNT(*) AS n FROM submissions')['n']);
        } finally { unlink($file); }
    }

    public function testInvalidQuantityRollsBackSubmissionAndRemovesTheNewlyStoredFile(): void
    {
        $before = glob(dirname(__DIR__, 2) . '/storage/uploads/*');
        $response = $this->evidence(['photo' => new CURLFile($this->png(), 'image/png', 'photo.png'), 'quantity' => '-1']);
        $this->assertSame(422, $response['status']);
        $this->assertSame(0, (int)one('SELECT COUNT(*) AS n FROM submissions')['n']);
        $this->assertSame($before, glob(dirname(__DIR__, 2) . '/storage/uploads/*'));
    }

    public function testPendingSubmissionLimitBlocksAnotherUpload(): void
    {
        $p = $this->user(); $c = $this->challenge();
        $this->membership((int)$p['id'], (int)$c['id']);
        $this->submission((int)$p['id'], (int)$c['id']);
        $this->loginAs($p);
        $response = $this->postAction('/user/submit-evidence-new-submission.php', [
            'action' => 'submit-evidence', 'challenge-id' => $c['id'], 'quantity' => '1',
            'evidence-description' => 'Another submission', 'photo' => new CURLFile($this->png(), 'image/png', 'new.png')]);
        $this->assertSame(422, $response['status']);
        $this->assertSame(1, (int)one('SELECT COUNT(*) AS n FROM submissions')['n']);
    }

    public function testRejectedEvidenceCanBeResubmittedWithNewImageAndDescription(): void
    {
        $p = $this->user(); $c = $this->challenge();
        $this->membership((int)$p['id'], (int)$c['id']);
        $old = $this->submission((int)$p['id'], (int)$c['id'], ['status' => 'rejected']);
        $this->loginAs($p);
        $response = $this->postAction('/user/submit-evidence-new-submission.php', [
            'action' => 'submit-evidence', 'challenge-id' => $c['id'], 'quantity' => '2',
            'evidence-description' => 'New clear photograph and corrected description',
            'photo' => new CURLFile($this->png(), 'image/png', 'replacement.png')]);
        $this->assertSame(303, $response['status']);
        $this->assertSame(2, (int)one('SELECT COUNT(*) AS n FROM submissions')['n']);
        $this->assertSame('rejected', one('SELECT status FROM submissions WHERE id=?', [$old['id']])['status']);
        $new = one("SELECT * FROM submissions WHERE status='pending'");
        $this->assertSame('New clear photograph and corrected description', $new['description']);
        $this->assertNotSame($old['photo'], $new['photo']);
    }

    /** @group requirements */
    public function testEvidencePickerOffersHeicAndHeifAlongsideJpegAndPng(): void
    {
        $this->loginAs($this->user());
        $response = $this->request('/user/submit-evidence-new-submission.php');
        $document = new DOMDocument();
        $before = libxml_use_internal_errors(true);
        $document->loadHTML($response['body']);
        libxml_clear_errors(); libxml_use_internal_errors($before);
        $xpath = new DOMXPath($document);
        $picker = $xpath->query('//input[@type="file" and @name="photo"]')->item(0);
        $this->assertNotNull($picker, 'Evidence needs a photo picker.');
        $accepted = array_map('trim', explode(',', strtolower($picker->getAttribute('accept'))));
        foreach (['heic', 'heif'] as $format) {
            $this->assertTrue(in_array('image/' . $format, $accepted, true) || in_array('.' . $format, $accepted, true),
                'The agreed phone-photo formats include ' . strtoupper($format) . '.');
        }
    }

    /** @group requirements */
    public function testHighResolution48MegapixelPhonePngCanBeSubmitted(): void
    {
        $response = $this->evidence(['photo' => new CURLFile($this->png(8000, 6000), 'image/png', '48mp.png')]);
        $this->assertSame(303, $response['status'], 'Full-resolution phone photos must not be rejected by the current 40MP ceiling.');
    }

    /** @group requirements */
    public function testPhotoLargerThanFiveMegabytesCanBeSubmitted(): void
    {
        // Valid PNG with a standard ancillary text chunk; well below the agreed 30 MB buffer alone.
        $response = $this->evidence(['photo' => new CURLFile($this->png(1, 1, 6 * 1000000), 'image/png', 'large.png')]);
        $this->assertSame(303, $response['status'], 'A 5MB cap cannot accommodate the requested phone-photo allowance. Exact upper cap is still undecided.');
    }

    /**
     * @group requirements
     * @dataProvider optionalReviewNotes
     */
    public function testReviewNotesAreOptionalForEveryDecision(string $decision): void
    {
        $p = $this->user(); $c = $this->challenge();
        $s = $this->submission((int)$p['id'], (int)$c['id']);
        $this->loginAs($this->user('coordinator'));
        $response = $this->postAction('/coordinator/submissions-review-submission.php', [
            'action' => 'review', 'id' => $s['id'], 'decision' => $decision, 'review-notes' => '',
            'review-matches' => '1', 'review-count' => '1', 'review-duplicate' => '1']);
        $this->assertSame(303, $response['status'], 'Review notes are optional, including rejection and revision.');
        $this->assertSame($decision, one('SELECT status FROM submissions WHERE id=?', [$s['id']])['status']);
    }

    public static function optionalReviewNotes(): array
    {
        return [['approved'], ['rejected'], ['needs-revision']];
    }

    /** @group requirements */
    public function testDeletingAnAccountWithEvidenceRemovesTheAccountPermanently(): void
    {
        $p = $this->user(); $c = $this->challenge();
        $this->submission((int)$p['id'], (int)$c['id'], ['status' => 'rejected']);
        $this->loginAs($this->user('admin'));
        $response = $this->postAction('/admin/users-delete-user.php', ['action' => 'delete-user', 'id' => $p['id']]);
        $this->assertSame(303, $response['status'], 'The agreed rule deletes accounts; evidence history must not force account retention. Linked-record handling remains a separate decision.');
        $this->assertNull(one('SELECT * FROM users WHERE id=?', [$p['id']]));
    }
}
