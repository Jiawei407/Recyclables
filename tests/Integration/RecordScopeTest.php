<?php

require_once dirname(__DIR__) . '/Support/DatabaseTestCase.php';

final class RecordScopeTest extends DatabaseTestCase
{
    public function testParticipantSubmissionListContainsOnlyTheirOwnEvidence(): void
    {
        $owner = $this->account();
        $other = $this->account();
        $challenge = $this->challenge();
        $ownId = $this->submission((int) $owner['id'], $challenge);
        $this->submission((int) $other['id'], $challenge);
        $this->setContext($owner, 'submit-evidence-submission-status');
        $this->assertSame([$ownId], $this->ids(page_records('submissions')));
    }

    public function testFacultySubmissionListExcludesOtherFaculties(): void
    {
        $representative = $this->account(['role' => 'faculty']);
        $student = $this->account();
        $outsider = $this->account(['faculty' => 'School of Engineering']);
        $challenge = $this->challenge();
        $ownId = $this->submission((int) $student['id'], $challenge);
        $this->submission((int) $outsider['id'], $challenge);
        $this->setContext($representative);
        $this->assertSame([$ownId], $this->ids(page_records('submissions')));
    }

    /** @dataProvider reviewQueues */
    public function testCoordinatorQueueReturnsOnlyItsStatus(string $queue): void
    {
        $coordinator = $this->account(['role' => 'coordinator']);
        $student = $this->account();
        $challenge = $this->challenge();
        $wanted = null;
        foreach (['pending', 'approved', 'rejected', 'needs-revision'] as $status) {
            $id = $this->submission((int) $student['id'], $challenge, $status);
            if ($status === $queue) $wanted = $id;
        }
        $this->setContext($coordinator, 'submissions-' . $queue);
        $this->assertSame([$wanted], $this->ids(page_records('submissions')));
    }

    public static function reviewQueues(): array
    {
        return [['pending'], ['approved'], ['rejected'], ['needs-revision']];
    }

    public function testPublicChallengeListIncludesOnlyCurrentlyOpenChallenges(): void
    {
        $open = $this->challenge();
        $this->challenge(['status' => 'draft']);
        $this->challenge(['status' => 'closed']);
        $this->challenge(['end_date' => '2001-01-01']);
        $this->challenge(['start_date' => '2099-01-01']);
        $this->assertSame([$open], $this->ids(page_records('challenges')));
    }

    public function testMyChallengesAndCompletedChallengesUseMembershipAndApprovedEvidence(): void
    {
        $student = $this->account();
        $other = $this->account();
        $joined = $this->challenge();
        $completed = $this->challenge();
        $unjoined = $this->challenge();
        foreach ([$joined, $completed] as $id) {
            query('INSERT INTO user_challenges (user_id,challenge_id) VALUES (?,?)', [$student['id'], $id]);
        }
        query('INSERT INTO user_challenges (user_id,challenge_id) VALUES (?,?)', [$other['id'], $unjoined]);
        $this->submission((int) $student['id'], $joined, 'pending');
        $this->submission((int) $student['id'], $completed, 'approved');
        $this->setContext($student, 'challenges-my-challenges');
        $this->assertEqualsCanonicalizing([$joined, $completed], $this->ids(page_records('challenges')));
        $this->setContext($student, 'challenges-completed');
        $this->assertSame([$completed], $this->ids(page_records('challenges')));
    }

    public function testFacultyChallengeStatisticsUseDistinctCompletedParticipants(): void
    {
        $representative = $this->account(['role' => 'faculty']);
        $student = $this->account();
        $outsider = $this->account(['faculty' => 'School of Engineering']);
        $challenge = $this->challenge();
        foreach ([$student, $outsider] as $account) {
            query('INSERT INTO user_challenges (user_id,challenge_id) VALUES (?,?)', [$account['id'], $challenge]);
        }
        $this->submission((int) $student['id'], $challenge, 'approved');
        $this->submission((int) $student['id'], $challenge, 'approved');
        $this->submission((int) $outsider['id'], $challenge, 'approved');
        $this->setContext($representative, 'participation-challenge-participation');
        $record = page_records('challenges')[0];
        $this->assertSame(1, (int) $record['joined']);
        $this->assertSame(1, (int) $record['completed']);
    }

    public function testMonthlyActionsExcludeUnapprovedAndOtherFacultyEvidence(): void
    {
        $representative = $this->account(['role' => 'faculty']);
        $student = $this->account();
        $outsider = $this->account(['faculty' => 'School of Engineering']);
        $challenge = $this->challenge();
        $this->submission((int) $student['id'], $challenge, 'approved', 1, '2025-01-15 12:00:00');
        $this->submission((int) $student['id'], $challenge, 'approved', 1, '2025-01-20 12:00:00');
        $this->submission((int) $student['id'], $challenge, 'pending', 1, '2025-01-21 12:00:00');
        $this->submission((int) $outsider['id'], $challenge, 'approved', 1, '2025-02-01 12:00:00');
        $this->setContext($representative);
        $records = page_records('actions-by-month');
        $this->assertCount(1, $records);
        $this->assertSame('2025-01', $records[0]['period']);
        $this->assertSame(2, (int) $records[0]['actions']);
    }

    public function testMonthlyParticipantsCountEachUserOnceAcrossChallenges(): void
    {
        $student = $this->account();
        foreach ([$this->challenge(), $this->challenge()] as $challenge) {
            query('INSERT INTO user_challenges (user_id,challenge_id,joined_at) VALUES (?,?,?)', [$student['id'], $challenge, '2025-01-15 12:00:00']);
        }
        $records = page_records('participants-by-month');
        $this->assertCount(1, $records);
        $this->assertSame('2025-01', $records[0]['period']);
        $this->assertSame(1, (int) $records[0]['participants']);
    }

    public function testFacultyReportsAreScopedButAdministratorCanReadAllReports(): void
    {
        $representative = $this->account(['role' => 'faculty']);
        foreach (['School of Computing', 'School of Engineering'] as $faculty) {
            query('INSERT INTO faculty_reports (activity,period,participants,outcome,faculty) VALUES (?,?,?,?,?)', ['Cleanup', 'October', 5, 'Collected litter', $faculty]);
        }
        $this->setContext($representative);
        $reports = page_records('reports');
        $this->assertCount(1, $reports);
        $this->assertSame('School of Computing', $reports[0]['faculty']);
        $this->setContext($this->account(['role' => 'admin']));
        $this->assertCount(2, page_records('reports'));
    }
}
