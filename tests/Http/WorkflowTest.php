<?php

use Recyclables\Tests\Support\HttpTestCase;

/** Runs real PHP requests against a disposable test database, never the live site. */
final class WorkflowTest extends HttpTestCase
{
    /** @dataProvider dashboardRoles */
    public function testValidLoginOpensTheCorrectRoleWorkspace(string $role): void
    {
        $account = $this->user($role);
        $response = $this->postAction('/login.php', [
            'action' => 'login', 'email' => $account['email'], 'password' => $account['test_password'],
        ]);

        $this->assertRedirect($response, '/' . $role . '/home.php', 302);
        self::assertSame(200, $this->request('/' . $role . '/home.php')['status']);
    }

    public static function dashboardRoles(): array
    {
        return [['user'], ['faculty'], ['coordinator'], ['admin']];
    }

    public function testInvalidPasswordDoesNotCreateAnAuthenticatedSession(): void
    {
        $account = $this->user();
        $response = $this->postAction('/login.php', [
            'action' => 'login', 'email' => $account['email'], 'password' => 'wrong-password!',
        ]);

        self::assertSame(422, $response['status']);
        self::assertStringContainsString('Email or password is incorrect', $response['body']);
        $this->assertRedirect($this->request('/user/home.php'), '/login.php', 302);
    }

    public function testSuspendedAccountCannotLogInWithItsCorrectPassword(): void
    {
        $account = $this->user('user', ['status' => 'suspended']);
        $response = $this->postAction('/login.php', [
            'action' => 'login', 'email' => $account['email'], 'password' => $account['test_password'],
        ]);

        self::assertSame(422, $response['status']);
        $this->assertRedirect($this->request('/user/home.php'), '/login.php', 302);
    }

    public function testLogoutEndsProtectedPageAccess(): void
    {
        $this->loginAs($this->user());

        $response = $this->postAction('/user/home.php', ['action' => 'logout']);

        $this->assertRedirect($response, '/login.php', 302);
        $this->assertRedirect($this->request('/user/home.php'), '/login.php', 302);
    }

    public function testMissingCsrfTokenRejectsTheActionWithoutJoining(): void
    {
        $account = $this->user();
        $challenge = $this->challenge();
        $this->loginAs($account);

        $response = $this->request('/user/challenges-browse.php', [
            'action' => 'join', 'id' => $challenge['id'],
        ]);

        self::assertSame(403, $response['status']);
        self::assertSame(0, (int) one('SELECT COUNT(*) AS total FROM user_challenges')['total']);
    }

    public function testForgedCsrfTokenRejectsTheActionWithoutJoining(): void
    {
        $account = $this->user();
        $challenge = $this->challenge();
        $this->loginAs($account);

        $response = $this->request('/user/challenges-browse.php', [
            'action' => 'join', 'id' => $challenge['id'], 'csrf' => str_repeat('x', 64),
        ]);

        self::assertSame(403, $response['status']);
        self::assertSame(0, (int) one('SELECT COUNT(*) AS total FROM user_challenges')['total']);
    }

    /** @dataProvider privilegedPages */
    public function testParticipantCannotOpenAnotherRolesPage(string $path): void
    {
        $this->loginAs($this->user());

        self::assertSame(403, $this->request($path)['status']);
    }

    public static function privilegedPages(): array
    {
        return [['/admin/users-users.php'], ['/coordinator/submissions-pending.php'], ['/faculty/home.php']];
    }

    public function testRegistrationStoresAHashedPasswordAndParticipantRole(): void
    {
        $fields = $this->registrationFields();
        // An untrusted public form cannot grant an administrator role.
        $fields['designated-role'] = 'admin';
        $fields['role'] = 'admin';

        $response = $this->postAction('/register.php', $fields);

        $this->assertRedirect($response, '/login.php');
        $saved = one('SELECT * FROM users WHERE email=?', [$fields['email']]);
        self::assertNotNull($saved);
        self::assertSame('user', $saved['role']);
        self::assertNotSame($fields['password'], $saved['password']);
        self::assertTrue(password_verify($fields['password'], $saved['password']));
    }

    /** @dataProvider invalidRegistrationFields */
    public function testInvalidRegistrationCreatesNoAccount(string $field, string $value): void
    {
        $fields = $this->registrationFields();
        $fields[$field] = $value;

        $response = $this->postAction('/register.php', $fields);

        self::assertSame(422, $response['status']);
        self::assertSame(0, (int) one('SELECT COUNT(*) AS total FROM users')['total']);
    }

    public static function invalidRegistrationFields(): array
    {
        return [
            'blank name' => ['full-name', '   '],
            'wrong domain' => ['email', 'student@example.com'],
            'suffix attack' => ['email', 'student@mail.apu.edu.my.example.com'],
            'wrong ID length' => ['student-id', 'TP12345'],
            'invalid faculty' => ['faculty', 'Invented faculty'],
            'confirmation mismatch' => ['confirm-password', 'different123!'],
            'weak password' => ['password', 'short!'],
        ];
    }

    public function testDuplicateEmailRegistrationReturnsAnErrorWithoutAnotherAccount(): void
    {
        $existing = $this->user();
        $fields = $this->registrationFields();
        $fields['email'] = $existing['email'];

        $response = $this->postAction('/register.php', $fields);

        self::assertSame(422, $response['status']);
        self::assertSame(1, (int) one('SELECT COUNT(*) AS total FROM users')['total']);
        self::assertStringNotContainsString('mysqli_sql_exception', $response['body']);
    }

    public function testProfileUpdateChangesOnlyTheLoggedInAccount(): void
    {
        $owner = $this->user();
        $other = $this->user();
        $this->loginAs($owner);

        $response = $this->postAction('/user/profile-edit-details.php', [
            'action' => 'profile', 'id' => $other['id'], 'profile-name' => 'Edited participant',
            'profile-email' => $owner['email'], 'profile-id' => $owner['university_id'],
            'profile-faculty' => $owner['faculty'], 'role' => 'admin',
        ]);

        $this->assertRedirect($response, '/user/profile-profile.php');
        $saved = one('SELECT * FROM users WHERE id=?', [$owner['id']]);
        self::assertSame('Edited participant', $saved['full_name']);
        self::assertSame('user', $saved['role']);
        self::assertSame($other['full_name'], one('SELECT * FROM users WHERE id=?', [$other['id']])['full_name']);
    }

    public function testJoiningTheSameChallengeTwiceCreatesOneMembership(): void
    {
        $account = $this->user();
        $challenge = $this->challenge();
        $this->loginAs($account);

        for ($i = 0; $i < 2; $i++) {
            $this->assertRedirect($this->postAction('/user/challenges-browse.php', [
                'action' => 'join', 'id' => $challenge['id'],
            ]), '/user/challenges-my-challenges.php');
        }

        self::assertSame(1, (int) one('SELECT COUNT(*) AS total FROM user_challenges WHERE user_id=? AND challenge_id=?', [$account['id'], $challenge['id']])['total']);
    }

    public function testJoinPermissionIsEnforcedOnTheServer(): void
    {
        $account = $this->user('user', ['can_join' => 0]);
        $challenge = $this->challenge();
        $this->loginAs($account);

        $response = $this->postAction('/user/challenges-browse.php', ['action' => 'join', 'id' => $challenge['id']]);

        self::assertSame(422, $response['status']);
        self::assertSame(0, (int) one('SELECT COUNT(*) AS total FROM user_challenges')['total']);
    }

    /** @dataProvider unavailableChallenges */
    public function testIneligibleOrUnavailableChallengeCannotBeJoined(array $changes): void
    {
        $account = $this->user();
        $challenge = $this->challenge($changes);
        $this->loginAs($account);

        $response = $this->postAction('/user/challenges-browse.php', ['action' => 'join', 'id' => $challenge['id']]);

        self::assertSame(422, $response['status']);
        self::assertSame(0, (int) one('SELECT COUNT(*) AS total FROM user_challenges')['total']);
    }

    public static function unavailableChallenges(): array
    {
        return [
            'closed' => [['status' => 'closed']],
            'draft' => [['status' => 'draft']],
            'staff only' => [['eligibility' => 'Staff']],
            'expired' => [['start_date' => '2000-01-01', 'end_date' => '2000-12-31']],
            'not started' => [['start_date' => '2099-01-01', 'end_date' => '2099-12-31']],
        ];
    }

    public function testApprovalAwardsPointsOnceAndRepeatReviewCannotChangeTheDecision(): void
    {
        $participant = $this->user();
        $coordinator = $this->user('coordinator');
        $challenge = $this->challenge(['points' => 30]);
        $submission = $this->submission($participant['id'], $challenge['id']);
        $this->loginAs($coordinator);
        $fields = $this->approvalFields($submission['id']);

        $this->assertRedirect($this->postAction('/coordinator/submissions-pending.php', $fields), '/coordinator/submissions-approved.php');
        self::assertSame(422, $this->postAction('/coordinator/submissions-pending.php', $fields)['status']);

        $saved = one('SELECT * FROM submissions WHERE id=?', [$submission['id']]);
        self::assertSame('approved', $saved['status']);
        self::assertSame(30, (int) $saved['points_awarded']);
        self::assertSame((int) $coordinator['id'], (int) $saved['reviewer_id']);
        self::assertSame(1, (int) one('SELECT COUNT(*) AS total FROM point_transactions WHERE submission_id=?', [$submission['id']])['total']);
        self::assertSame(30, (int) one('SELECT SUM(points) AS total FROM point_transactions WHERE user_id=?', [$participant['id']])['total']);
    }

    /** @dataProvider reviewerChecks */
    public function testMissingApprovalCheckLeavesTheSubmissionPending(string $missing): void
    {
        $participant = $this->user();
        $challenge = $this->challenge();
        $submission = $this->submission($participant['id'], $challenge['id']);
        $this->loginAs($this->user('coordinator'));
        $fields = $this->approvalFields($submission['id']);
        unset($fields[$missing]);

        $response = $this->postAction('/coordinator/submissions-pending.php', $fields);

        self::assertSame(422, $response['status']);
        self::assertSame('pending', one('SELECT status FROM submissions WHERE id=?', [$submission['id']])['status']);
        self::assertSame(0, (int) one('SELECT COUNT(*) AS total FROM point_transactions')['total']);
    }

    public static function reviewerChecks(): array
    {
        return [['review-matches'], ['review-count'], ['review-duplicate']];
    }

    /** @dataProvider nonApprovalDecisions */
    public function testNonApprovalDecisionIsSavedWithoutPointAwards(string $decision): void
    {
        $participant = $this->user();
        $challenge = $this->challenge();
        $submission = $this->submission($participant['id'], $challenge['id']);
        $this->loginAs($this->user('coordinator'));

        $response = $this->postAction('/coordinator/submissions-pending.php', [
            'action' => 'review', 'id' => $submission['id'], 'decision' => $decision,
            'review-notes' => 'Please use a clearer image.',
        ]);

        $this->assertRedirect($response, '/coordinator/submissions-' . $decision . '.php');
        $saved = one('SELECT * FROM submissions WHERE id=?', [$submission['id']]);
        self::assertSame($decision, $saved['status']);
        self::assertSame('Please use a clearer image.', $saved['review_notes']);
        self::assertSame(0, (int) $saved['points_awarded']);
        self::assertSame(0, (int) one('SELECT COUNT(*) AS total FROM point_transactions')['total']);
    }

    public static function nonApprovalDecisions(): array
    {
        return [['rejected'], ['needs-revision']];
    }

    /** @dataProvider finalStatuses */
    public function testExistingReviewDecisionCannotBeReversed(string $status): void
    {
        $participant = $this->user();
        $challenge = $this->challenge();
        $submission = $this->submission($participant['id'], $challenge['id'], ['status' => $status]);
        $this->loginAs($this->user('coordinator'));
        $fields = $this->approvalFields($submission['id']);
        $fields['decision'] = $status === 'approved' ? 'rejected' : 'approved';
        $fields['review-notes'] = 'Attempt to change a completed review.';

        self::assertSame(422, $this->postAction('/coordinator/submissions-pending.php', $fields)['status']);
        self::assertSame($status, one('SELECT status FROM submissions WHERE id=?', [$submission['id']])['status']);
        self::assertSame(0, (int) one('SELECT COUNT(*) AS total FROM point_transactions')['total']);
    }

    public static function finalStatuses(): array
    {
        return [['approved'], ['rejected'], ['needs-revision']];
    }

    public function testParticipantCannotReadAnotherParticipantsEvidence(): void
    {
        $owner = $this->user();
        $challenge = $this->challenge();
        $submission = $this->submission($owner['id'], $challenge['id']);
        $this->loginAs($this->user());

        $response = $this->request('/user/submit-evidence-submission-details.php?id=' . $submission['id']);

        self::assertSame(404, $response['status']);
        self::assertStringNotContainsString($submission['description'], $response['body']);
    }

    public function testValidDeductionUpdatesBalanceAndAuditHistory(): void
    {
        $participant = $this->user();
        query('INSERT INTO point_transactions (user_id,points,reason) VALUES (?,?,?)', [$participant['id'], 200, 'Starting fixture points']);
        $this->loginAs($this->user('admin'));

        $response = $this->postAction('/admin/leaderboard-point-corrections.php', [
            'action' => 'correct-points', 'id' => $participant['id'], 'decision' => 'deduct',
            'points-to-deduct' => '20', 'review-reason' => 'Duplicate evidence confirmed.',
        ]);

        $this->assertRedirect($response, '/admin/leaderboard-point-corrections.php');
        self::assertSame(180, (int) one('SELECT SUM(points) AS total FROM point_transactions WHERE user_id=?', [$participant['id']])['total']);
        self::assertSame(1, (int) one("SELECT COUNT(*) AS total FROM audit_logs WHERE action='points-deduct'")['total']);
    }

    /** @dataProvider invalidDeductions */
    public function testInvalidDeductionLeavesTheBalanceUnchanged(string $amount): void
    {
        $participant = $this->user();
        query('INSERT INTO point_transactions (user_id,points,reason) VALUES (?,?,?)', [$participant['id'], 200, 'Starting fixture points']);
        $this->loginAs($this->user('admin'));

        $response = $this->postAction('/admin/leaderboard-point-corrections.php', [
            'action' => 'correct-points', 'id' => $participant['id'], 'decision' => 'deduct',
            'points-to-deduct' => $amount, 'review-reason' => 'Review fixture.',
        ]);

        self::assertSame(422, $response['status']);
        self::assertSame(200, (int) one('SELECT SUM(points) AS total FROM point_transactions WHERE user_id=?', [$participant['id']])['total']);
        self::assertSame(0, (int) one('SELECT COUNT(*) AS total FROM audit_logs')['total']);
    }

    public static function invalidDeductions(): array
    {
        return [['201'], ['0'], ['-1'], ['1.5'], ['not-a-number']];
    }

    /** @dataProvider pointReviewDecisions */
    public function testApprovingOrDenyingPointReviewDoesNotItselfChangeTheBalance(string $decision): void
    {
        $participant = $this->user();
        query('INSERT INTO point_transactions (user_id,points,reason) VALUES (?,?,?)', [$participant['id'], 200, 'Starting fixture points']);
        $this->loginAs($this->user('admin'));

        $response = $this->postAction('/admin/leaderboard-point-corrections.php', [
            'action' => 'correct-points', 'id' => $participant['id'], 'decision' => $decision,
            'review-reason' => 'Reviewed duplicate concern.', 'points-to-deduct' => '20',
        ]);

        $this->assertRedirect($response, '/admin/leaderboard-point-corrections.php');
        self::assertSame(200, (int) one('SELECT SUM(points) AS total FROM point_transactions WHERE user_id=?', [$participant['id']])['total']);
        self::assertSame(1, (int) one('SELECT COUNT(*) AS total FROM point_transactions')['total']);
    }

    public static function pointReviewDecisions(): array
    {
        return [['approve'], ['deny']];
    }

    public function testFacultyReportIgnoresForgedFacultyAndBelongsToTheAuthor(): void
    {
        $faculty = $this->user('faculty', ['faculty' => 'School of Computing']);
        $this->loginAs($faculty);

        $response = $this->postAction('/faculty/submit-report-submit-report.php', [
            'action' => 'save-report', 'report-activity' => 'Computing cleanup',
            'report-period' => 'Current month', 'report-participants' => '12',
            'report-outcome' => 'Collected and weighed litter.', 'faculty' => 'School of Engineering',
        ]);

        $this->assertRedirect($response, '/faculty/submit-report-reports.php');
        $saved = one('SELECT * FROM faculty_reports');
        self::assertSame('School of Computing', $saved['faculty']);
        self::assertSame((int) $faculty['id'], (int) $saved['created_by']);
        self::assertSame(12, (int) $saved['participants']);
    }

    public function testFacultyCannotReadAnotherFacultysPrivateReport(): void
    {
        $author = $this->user('faculty', ['faculty' => 'School of Engineering']);
        query('INSERT INTO faculty_reports (activity,period,participants,outcome,faculty,created_by) VALUES (?,?,?,?,?,?)', [
            'Private engineering activity', 'Current month', 12, 'Private engineering outcome', $author['faculty'], $author['id'],
        ]);
        $reportId = mysqli_insert_id($GLOBALS['conn']);
        $this->loginAs($this->user('faculty', ['faculty' => 'School of Computing']));

        $response = $this->request('/faculty/submit-report-activity-report.php?id=' . $reportId);

        self::assertSame(404, $response['status']);
        self::assertStringNotContainsString('Private engineering outcome', $response['body']);
    }

    /** @dataProvider roleListPages */
    public function testRoleListingRemainsAvailable(string $role, string $path): void
    {
        $this->loginAs($this->user($role));

        $response = $this->request($path);

        self::assertSame(200, $response['status'], $path);
        self::assertStringContainsString('<main', $response['body']);
        self::assertStringNotContainsString('Fatal error', $response['body']);
    }

    public static function roleListPages(): array
    {
        return [
            ['user', '/user/challenges-browse.php'],
            ['user', '/user/submit-evidence-submission-status.php'],
            ['user', '/user/achievements-earned.php'],
            ['user', '/user/leaderboard-individual-ranking.php'],
            ['faculty', '/faculty/participation-challenge-participation.php'],
            ['faculty', '/faculty/promote-challenges-featured-for-faculty.php'],
            ['faculty', '/faculty/submit-report-reports.php'],
            ['coordinator', '/coordinator/challenges-all-challenges.php'],
            ['coordinator', '/coordinator/submissions-pending.php'],
            ['coordinator', '/coordinator/campaigns-campaigns.php'],
            ['coordinator', '/coordinator/announcements-all-announcements.php'],
            ['admin', '/admin/users-users.php'],
            ['admin', '/admin/achievements-achievements.php'],
            ['admin', '/admin/reports-faculty-reports.php'],
            ['admin', '/admin/impact-analytics.php'],
            ['admin', '/admin/audit-log.php'],
        ];
    }

    private function registrationFields(): array
    {
        return [
            'action' => 'register', 'full-name' => 'New registration fixture',
            'email' => 'registration.fixture@mail.apu.edu.my', 'student-id' => 'TP990001',
            'faculty' => 'School of Computing', 'password' => 'register123!',
            'confirm-password' => 'register123!',
        ];
    }

    private function approvalFields(int $submissionId): array
    {
        return [
            'action' => 'review', 'id' => $submissionId, 'decision' => 'approved',
            'review-notes' => '', 'review-matches' => '1', 'review-count' => '1', 'review-duplicate' => '1',
        ];
    }

    private function assertRedirect(array $response, string $path, int $status = 303): void
    {
        self::assertSame($status, $response['status'], substr($response['body'], 0, 500));
        self::assertArrayHasKey('location', $response['headers']);
        self::assertSame($path, parse_url($response['headers']['location'], PHP_URL_PATH));
    }
}
