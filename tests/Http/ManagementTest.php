<?php

use Recyclables\Tests\Support\HttpTestCase;

/** Real management requests; fixtures belong only to the disposable test schema. */
final class ManagementTest extends HttpTestCase
{
    public function testPasswordChangeUpdatesTheHashAndMakesTheOldPasswordUnusable(): void
    {
        $account = $this->user();
        $this->loginAs($account);

        $response = $this->postAction('/user/profile-change-password.php', [
            'action' => 'password', 'password-current' => $account['test_password'],
            'password-new' => 'changed123!', 'password-confirm' => 'changed123!',
        ]);

        $this->assertSaved($response);
        $saved = one('SELECT password FROM users WHERE id=?', [$account['id']]);
        self::assertTrue(password_verify('changed123!', $saved['password']));
        self::assertFalse(password_verify($account['test_password'], $saved['password']));
    }

    /** @dataProvider invalidPasswordChanges */
    public function testInvalidPasswordChangePreservesTheExistingHash(array $changes): void
    {
        $account = $this->user();
        $this->loginAs($account);
        $fields = array_merge([
            'action' => 'password', 'password-current' => $account['test_password'],
            'password-new' => 'changed123!', 'password-confirm' => 'changed123!',
        ], $changes);

        self::assertSame(422, $this->postAction('/user/profile-change-password.php', $fields)['status']);
        self::assertSame($account['password'], one('SELECT password FROM users WHERE id=?', [$account['id']])['password']);
    }

    public static function invalidPasswordChanges(): array
    {
        return ['wrong current' => [['password-current' => 'incorrect123!']],
            'mismatched confirmation' => [['password-confirm' => 'different123!']],
            'too short' => [['password-new' => 'short!', 'password-confirm' => 'short!']]];
    }

    public function testAdministratorCanEditAnAccountWithBlankPasswordWithoutReplacingItsHash(): void
    {
        $admin = $this->user('admin');
        $account = $this->user();
        $this->loginAs($admin);

        $this->assertSaved($this->postAction('/admin/users-edit-user.php', [
            'action' => 'save-user', 'id' => $account['id'], 'full-name' => 'Updated account name',
            'email' => $account['email'], 'university-id' => $account['university_id'],
            'faculty' => $account['faculty'], 'designated-role' => 'user', 'account-status' => 'active',
            'new-password' => '', 'confirm-password' => '', 'can-join' => '1', 'can-submit' => '1',
        ]));

        $saved = one('SELECT * FROM users WHERE id=?', [$account['id']]);
        self::assertSame('Updated account name', $saved['full_name']);
        self::assertSame($account['password'], $saved['password']);
    }

    public function testCoordinatorCreatesAChallengeWithItsScheduleAndRules(): void
    {
        $coordinator = $this->user('coordinator');
        $this->loginAs($coordinator);

        $this->assertSaved($this->postAction('/coordinator/challenges-create.php', $this->challengeFields()));

        $saved = one('SELECT * FROM challenges');
        self::assertSame('Managed challenge', $saved['title']);
        self::assertSame(30, (int) $saved['points']);
        self::assertSame('2000-01-01', $saved['start_date']);
        self::assertSame('2099-12-31', $saved['end_date']);
        self::assertSame(2, (int) $saved['submission_limit']);
        self::assertSame((int) $coordinator['id'], (int) $saved['created_by']);
    }

    /** @dataProvider invalidChallengeFields */
    public function testInvalidChallengeRulesCreateNoChallengeOrAuditEntry(string $field, string $value): void
    {
        $this->loginAs($this->user('coordinator'));
        $fields = array_replace($this->challengeFields(), [$field => $value]);

        self::assertSame(422, $this->postAction('/coordinator/challenges-create.php', $fields)['status']);
        self::assertSame(0, (int) one('SELECT COUNT(*) AS total FROM challenges')['total']);
        self::assertSame(0, (int) one('SELECT COUNT(*) AS total FROM audit_logs')['total']);
    }

    public static function invalidChallengeFields(): array
    {
        return ['negative reward' => ['challenge-points', '-1'],
            'excessive reward' => ['challenge-points', '1001'],
            'fractional reward' => ['challenge-points', '1.5'],
            'zero submission limit' => ['challenge-limit', '0'],
            'impossible calendar date' => ['challenge-end', '2026-02-30'],
            'end before start' => ['challenge-end', '1999-12-31']];
    }

    public function testEditingOneChallengeLeavesTheOtherChallengeUnchanged(): void
    {
        $edited = $this->challenge(['title' => 'First challenge']);
        $other = $this->challenge(['title' => 'Other challenge']);
        $this->loginAs($this->user('coordinator'));
        $fields = array_replace($this->challengeFields(), ['id' => $edited['id'], 'challenge-title' => 'Updated challenge']);

        $this->assertSaved($this->postAction('/coordinator/challenges-edit.php?id=' . $edited['id'], $fields));

        self::assertSame('Updated challenge', one('SELECT title FROM challenges WHERE id=?', [$edited['id']])['title']);
        self::assertSame($other, one('SELECT * FROM challenges WHERE id=?', [$other['id']]));
        self::assertSame(2, (int) one('SELECT COUNT(*) AS total FROM challenges')['total']);
    }

    /** @dataProvider invalidCampaignLinks */
    public function testCampaignRejectsInvalidLinkedChallengesWithoutPartialRecords($challengeIds): void
    {
        $this->loginAs($this->user('coordinator'));
        $fields = $this->campaignFields();
        $fields['campaign-challenges'] = $challengeIds;

        self::assertSame(422, $this->postAction('/coordinator/campaigns-create.php', $fields)['status']);
        self::assertSame(0, (int) one('SELECT COUNT(*) AS total FROM campaigns')['total']);
        self::assertSame(0, (int) one('SELECT COUNT(*) AS total FROM campaign_challenges')['total']);
    }

    public static function invalidCampaignLinks(): array
    {
        return ['no selection' => [[]], 'scalar instead of list' => ['not-a-list'],
            'missing challenge' => [['2147483647']]];
    }

    public function testCampaignDeduplicatesRepeatedChallengeSelections(): void
    {
        $challenge = $this->challenge();
        $this->loginAs($this->user('coordinator'));
        $fields = $this->campaignFields();
        $fields['campaign-challenges'] = [$challenge['id'], $challenge['id']];

        $this->assertSaved($this->postAction('/coordinator/campaigns-create.php', $fields));

        self::assertSame(1, (int) one('SELECT COUNT(*) AS total FROM campaigns')['total']);
        self::assertSame(1, (int) one('SELECT COUNT(*) AS total FROM campaign_challenges')['total']);
    }

    public function testCampaignBonusRequiresEveryIncludedChallengeAndIsAwardedOnlyOnce(): void
    {
        $account = $this->user();
        $first = $this->challenge(['title' => 'First campaign challenge']);
        $second = $this->challenge(['title' => 'Second campaign challenge']);
        $a = $this->submission($account['id'], $first['id']);
        $b = $this->submission($account['id'], $second['id']);
        $extra = $this->submission($account['id'], $first['id']);
        $this->loginAs($this->user('coordinator'));
        $fields = $this->campaignFields();
        $fields['campaign-challenges'] = [$first['id'], $second['id']];
        $this->assertSaved($this->postAction('/coordinator/campaigns-create.php', $fields));

        $this->approve($a['id']);
        self::assertSame(0, (int) one('SELECT COUNT(*) AS total FROM campaign_awards')['total']);
        $this->approve($b['id']);
        self::assertSame(1, (int) one('SELECT COUNT(*) AS total FROM campaign_awards')['total']);
        self::assertSame(70, (int) one('SELECT SUM(points) AS total FROM point_transactions WHERE user_id=?', [$account['id']])['total']);
        $this->approve($extra['id']);
        self::assertSame(1, (int) one('SELECT COUNT(*) AS total FROM campaign_awards')['total']);
        self::assertSame(100, (int) one('SELECT SUM(points) AS total FROM point_transactions WHERE user_id=?', [$account['id']])['total']);
        self::assertSame(1, (int) one("SELECT COUNT(*) AS total FROM point_transactions WHERE reason='Campaign completed: Managed campaign'")['total']);
    }

    public function testEvidenceSubmittedDuringCampaignStillEarnsItsBonusWhenReviewedLater(): void
    {
        $account = $this->user();
        $challenge = $this->challenge();
        $submission = $this->submission($account['id'], $challenge['id'], ['created_at' => '2020-06-15 12:00:00']);
        $this->loginAs($this->user('coordinator'));
        $fields = array_replace($this->campaignFields(), [
            'campaign-start' => '2020-01-01', 'campaign-end' => '2020-12-31',
            'campaign-challenges' => [$challenge['id']],
        ]);
        $this->assertSaved($this->postAction('/coordinator/campaigns-create.php', $fields));

        $this->approve($submission['id']);

        self::assertSame(1, (int) one('SELECT COUNT(*) AS total FROM campaign_awards')['total']);
        self::assertSame(40, (int) one('SELECT SUM(points) AS total FROM point_transactions WHERE user_id=?', [$account['id']])['total']);
    }

    public function testEvidenceOutsideCampaignDatesDoesNotEarnTheBonus(): void
    {
        $account = $this->user();
        $challenge = $this->challenge();
        $submission = $this->submission($account['id'], $challenge['id'], ['created_at' => '2019-12-31 23:59:59']);
        $this->loginAs($this->user('coordinator'));
        $fields = array_replace($this->campaignFields(), [
            'campaign-start' => '2020-01-01', 'campaign-end' => '2020-12-31',
            'campaign-challenges' => [$challenge['id']],
        ]);
        $this->assertSaved($this->postAction('/coordinator/campaigns-create.php', $fields));

        $this->approve($submission['id']);

        self::assertSame(0, (int) one('SELECT COUNT(*) AS total FROM campaign_awards')['total']);
        self::assertSame(30, (int) one('SELECT SUM(points) AS total FROM point_transactions WHERE user_id=?', [$account['id']])['total']);
    }

    public function testCampaignWithAwardedRewardsCannotBeEdited(): void
    {
        $account = $this->user();
        $challenge = $this->challenge();
        $submission = $this->submission($account['id'], $challenge['id']);
        $this->loginAs($this->user('coordinator'));
        $fields = $this->campaignFields();
        $fields['campaign-challenges'] = [$challenge['id']];
        $this->assertSaved($this->postAction('/coordinator/campaigns-create.php', $fields));
        $campaign = one('SELECT * FROM campaigns');
        $this->approve($submission['id']);

        $fields['id'] = $campaign['id'];
        $fields['campaign-bonus'] = '999';
        self::assertSame(422, $this->postAction('/coordinator/campaigns-edit.php?id=' . $campaign['id'], $fields)['status']);

        self::assertSame($campaign, one('SELECT * FROM campaigns WHERE id=?', [$campaign['id']]));
    }

    public function testFacultyActivityUsesTheAuthorsFacultyAndCapacity(): void
    {
        $faculty = $this->user('faculty');
        $this->loginAs($faculty);
        $fields = $this->activityFields();
        $fields['faculty'] = 'School of Engineering';

        $this->assertSaved($this->postAction('/faculty/faculty-activities-create-activity.php', $fields));

        $saved = one('SELECT * FROM faculty_activities');
        self::assertSame($faculty['faculty'], $saved['faculty']);
        self::assertSame((int) $faculty['id'], (int) $saved['created_by']);
        self::assertSame(30, (int) $saved['participant_limit']);
        self::assertSame('2030-06-15 10:30:00', $saved['activity_date']);
    }

    /** @dataProvider invalidActivityFields */
    public function testInvalidActivityDetailsOrCapacityCreateNoActivity(string $field, string $value): void
    {
        $this->loginAs($this->user('faculty'));
        $fields = array_replace($this->activityFields(), [$field => $value]);

        self::assertSame(422, $this->postAction('/faculty/faculty-activities-create-activity.php', $fields)['status']);
        self::assertSame(0, (int) one('SELECT COUNT(*) AS total FROM faculty_activities')['total']);
    }

    public static function invalidActivityFields(): array
    {
        return ['missing name' => ['activity-name', '   '], 'missing location' => ['activity-location', ''],
            'missing details' => ['activity-details', ''], 'invalid date' => ['activity-date', '2030-02-30T10:30'],
            'zero capacity' => ['activity-limit', '0'], 'fractional capacity' => ['activity-limit', '2.5']];
    }

    public function testFacultyAnnouncementCannotExpandItsAudienceOrChangeItsFaculty(): void
    {
        $faculty = $this->user('faculty');
        $this->loginAs($faculty);
        $fields = $this->announcementFields();
        $fields['announcement-audience'] = 'All users';
        $fields['faculty'] = 'School of Engineering';

        $this->assertSaved($this->postAction('/faculty/announcements-create.php', $fields));

        $saved = one('SELECT * FROM announcements');
        self::assertSame('My faculty', $saved['audience']);
        self::assertSame($faculty['faculty'], $saved['faculty']);
        self::assertSame((int) $faculty['id'], (int) $saved['created_by']);
    }

    /** @dataProvider unauthorizedAnnouncementAuthors */
    public function testFacultyCannotEditAnotherAuthorsAnnouncement(string $authorsFaculty): void
    {
        $author = $this->user('faculty', ['faculty' => $authorsFaculty]);
        query('INSERT INTO announcements (title,message,publish_date,audience,faculty,created_by) VALUES (?,?,?,?,?,?)',
            ['Original faculty notice', 'Unchanged message', '2000-01-01', 'My faculty', $author['faculty'], $author['id']]);
        $announcement = one('SELECT * FROM announcements');
        $this->loginAs($this->user('faculty', ['faculty' => 'School of Computing']));
        $fields = $this->announcementFields();
        $fields['id'] = $announcement['id'];

        // Cross-faculty records are concealed as missing by the page read model.
        $expectedStatus = $authorsFaculty === 'School of Computing' ? 422 : 404;
        self::assertSame($expectedStatus, $this->postAction('/faculty/announcements-edit.php?id=' . $announcement['id'], $fields)['status']);
        self::assertSame($announcement, one('SELECT * FROM announcements WHERE id=?', [$announcement['id']]));
    }

    public static function unauthorizedAnnouncementAuthors(): array
    {
        return ['different faculty' => ['School of Engineering'], 'same faculty different author' => ['School of Computing']];
    }

    public function testCoordinatorCannotPublishToAnUnknownAudience(): void
    {
        $this->loginAs($this->user('coordinator'));
        $fields = $this->announcementFields();
        $fields['announcement-audience'] = 'Unknown audience';

        self::assertSame(422, $this->postAction('/coordinator/announcements-create.php', $fields)['status']);
        self::assertSame(0, (int) one('SELECT COUNT(*) AS total FROM announcements')['total']);
    }

    public function testParticipantHomepageShowsOnlyNoticesForItsRoleAndFaculty(): void
    {
        $author = $this->user('coordinator');
        $notices = [
            ['Campus notice fixture', 'All users', null],
            ['Participant notice fixture', 'Registered participants', null],
            ['Computing notice fixture', 'My faculty', 'School of Computing'],
            ['Engineering secret fixture', 'My faculty', 'School of Engineering'],
            ['Coordinator secret fixture', 'Coordinators', null],
        ];
        foreach ($notices as $notice) {
            query('INSERT INTO announcements (title,message,publish_date,audience,faculty,created_by) VALUES (?,?,?,?,?,?)',
                [$notice[0], 'Audience fixture body', '2000-01-01', $notice[1], $notice[2], $author['id']]);
        }
        $this->loginAs($this->user('user', ['faculty' => 'School of Computing']));

        $page = $this->request('/user/home.php');

        self::assertSame(200, $page['status']);
        foreach (['Campus notice fixture', 'Participant notice fixture', 'Computing notice fixture'] as $visible) {
            self::assertStringContainsString($visible, $page['body']);
        }
        foreach (['Engineering secret fixture', 'Coordinator secret fixture'] as $hidden) {
            self::assertStringNotContainsString($hidden, $page['body']);
        }
    }

    private function challengeFields(): array
    {
        return ['action' => 'save-challenge', 'challenge-title' => 'Managed challenge',
            'challenge-description' => 'Collect recyclables safely.', 'challenge-category' => 'Recycling',
            'tracked-item' => 'Plastic bottles', 'challenge-points' => '30',
            'challenge-start' => '2000-01-01', 'challenge-end' => '2099-12-31',
            'challenge-limit' => '2', 'challenge-eligibility' => 'Students and staff',
            'challenge-completion' => 'One approved submission', 'challenge-status' => 'open'];
    }

    private function campaignFields(): array
    {
        return ['action' => 'save-campaign', 'campaign-name' => 'Managed campaign',
            'campaign-description' => 'Complete each included challenge.', 'campaign-target' => '100',
            'campaign-bonus' => '10', 'campaign-start' => '2000-01-01', 'campaign-end' => '2099-12-31',
            'campaign-badge' => ''];
    }

    private function activityFields(): array
    {
        return ['action' => 'save-activity', 'activity-name' => 'Faculty cleanup fixture',
            'activity-location' => 'Main entrance', 'activity-date' => '2030-06-15T10:30',
            'activity-limit' => '30', 'activity-details' => 'Collect litter around the faculty building.'];
    }

    private function announcementFields(): array
    {
        return ['action' => 'save-announcement', 'announcement-title' => 'Managed announcement',
            'announcement-message' => 'Campus sustainability update.',
            'announcement-date' => '2000-01-01', 'announcement-audience' => 'All users'];
    }

    private function approve(int $submissionId): void
    {
        $this->assertSaved($this->postAction('/coordinator/submissions-pending.php', [
            'action' => 'review', 'id' => $submissionId, 'decision' => 'approved',
            'review-notes' => '', 'review-matches' => '1', 'review-count' => '1', 'review-duplicate' => '1',
        ]));
    }

    private function assertSaved(array $response): void
    {
        self::assertSame(303, $response['status'], substr($response['body'], 0, 500));
        self::assertArrayHasKey('location', $response['headers']);
    }
}
