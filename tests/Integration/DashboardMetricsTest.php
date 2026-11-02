<?php

require_once dirname(__DIR__) . '/Support/DatabaseTestCase.php';
require_once dirname(__DIR__, 2) . '/includes/metrics.php';
require_once dirname(__DIR__, 2) . '/includes/dashboard.php';

final class DashboardMetricsTest extends DatabaseTestCase
{
    private function joinChallenge(int $accountId, int $challengeId, string $date = '2025-01-15 12:00:00'): void
    {
        query('INSERT INTO user_challenges (user_id,challenge_id,joined_at) VALUES (?,?,?)',[$accountId,$challengeId,$date]);
    }

    public function testFacultyParticipationCountsEachEligiblePersonOnceAndReturnsPercentage(): void
    {
        $representative=$this->account(['role'=>'faculty']);
        $joined=$this->account();
        $unjoined=$this->account();
        $suspended=$this->account(['status'=>'suspended']);
        $outsider=$this->account(['faculty'=>'School of Engineering']);
        $first=$this->challenge();
        $second=$this->challenge();
        foreach ([$representative,$joined,$suspended,$outsider] as $account) $this->joinChallenge((int)$account['id'],$first);
        $this->joinChallenge((int)$joined['id'],$second);
        $this->setContext($representative);
        self::assertSame(1,metric('Active participants'));
        self::assertSame('50%',metric('Participation rate'));
    }

    public function testParticipationRateForFacultyWithoutParticipantsIsZero(): void
    {
        $this->setContext($this->account(['role'=>'faculty']));
        self::assertSame('0%',metric('Participation rate'));
        self::assertSame(0,metric('Active participants'));
    }

    public function testMonthlyParticipationIsScopedToFacultyAndDeduplicatesPeople(): void
    {
        $representative=$this->account(['role'=>'faculty']);
        $student=$this->account();
        $outsider=$this->account(['faculty'=>'School of Engineering']);
        $inactive=$this->account(['status'=>'inactive']);
        $first=$this->challenge();
        $second=$this->challenge();
        $this->joinChallenge((int)$student['id'],$first);
        $this->joinChallenge((int)$student['id'],$second);
        $this->joinChallenge((int)$outsider['id'],$first);
        $this->joinChallenge((int)$inactive['id'],$first);
        $this->joinChallenge((int)$representative['id'],$first);
        $this->setContext($representative);
        $records=page_records('participants-by-month');
        self::assertCount(1,$records);
        self::assertSame('2025-01',$records[0]['period']);
        self::assertSame(1,(int)$records[0]['participants']);
        $this->setContext($student);
        self::assertSame(1,(int)page_records('participants-by-month')[0]['participants']);
    }

    public function testEmptyParticipantDashboardHasHonestEmptyRecords(): void
    {
        $student=$this->account();
        self::assertSame(['active_challenge'=>null,'milestone'=>null,'activity'=>[]],participant_dashboard((int)$student['id']));
    }

    public function testActiveChallengeUsesLatestJoinedOpenInDateChallengeOwnedByStudent(): void
    {
        $student=$this->account();
        $other=$this->account();
        $old=$this->challenge();
        $active=$this->challenge(['title'=>'My latest challenge']);
        $closed=$this->challenge(['status'=>'closed']);
        $future=$this->challenge(['start_date'=>'2098-01-01']);
        $expired=$this->challenge(['end_date'=>'2001-01-01']);
        $outside=$this->challenge(['title'=>'Other student challenge']);
        $this->joinChallenge((int)$student['id'],$old,'2025-01-01 00:00:00');
        $this->joinChallenge((int)$student['id'],$active,'2025-02-01 00:00:00');
        foreach ([$closed,$future,$expired] as $challenge) $this->joinChallenge((int)$student['id'],$challenge,'2025-03-01 00:00:00');
        $this->joinChallenge((int)$other['id'],$outside,'2025-04-01 00:00:00');
        $dashboard=participant_dashboard((int)$student['id']);
        self::assertSame($active,(int)$dashboard['active_challenge']['id']);
        self::assertSame('My latest challenge',$dashboard['active_challenge']['title']);
    }

    public function testActivityUsesOwnSavedSubmissionsPointsAndMembershipsInDateOrder(): void
    {
        $student=$this->account();
        $other=$this->account();
        $challenge=$this->challenge();
        $this->joinChallenge((int)$student['id'],$challenge,'2025-01-01 00:00:00');
        $submission=$this->submission((int)$student['id'],$challenge,'approved',3,'2025-01-02 00:00:00');
        query('UPDATE submissions SET reviewed_at=? WHERE id=?',['2025-01-04 00:00:00',$submission]);
        $this->points((int)$student['id'],20);
        query('UPDATE point_transactions SET created_at=? WHERE user_id=?',['2025-01-03 00:00:00',$student['id']]);
        $this->submission((int)$other['id'],$challenge,'rejected',99,'2025-01-05 00:00:00');
        $activity=participant_dashboard((int)$student['id'])['activity'];
        self::assertSame(['submission','points','joined'],array_column($activity,'kind'));
        self::assertSame('approved',$activity[0]['status']);
        self::assertSame(20,(int)$activity[1]['points']);
        self::assertSame('2025-01-04 00:00:00',$activity[0]['occurred_at']);
    }

    public function testActivityListIsBoundedToFiveLatestRecords(): void
    {
        $student=$this->account();
        $challenge=$this->challenge();
        for ($day=1;$day<=7;$day++) $this->submission((int)$student['id'],$challenge,'pending',1,sprintf('2025-01-%02d 00:00:00',$day));
        $activity=participant_dashboard((int)$student['id'])['activity'];
        self::assertCount(5,$activity);
        self::assertSame('2025-01-07 00:00:00',$activity[0]['occurred_at']);
        self::assertSame('2025-01-03 00:00:00',$activity[4]['occurred_at']);
    }

    public function testNextMilestoneShowsRealUnmetRequirementsAndSkipsEarnedOrDraftBadges(): void
    {
        $student=$this->account();
        $challenge=$this->challenge();
        $earned=$this->badge([[2,'Plastic bottles','At least']]);
        $draft=$this->badge([[4,'Plastic bottles','At least']]);
        query("UPDATE achievements SET status='draft' WHERE id=?",[$draft]);
        $next=$this->badge([[5,'Plastic bottles','At least']]);
        $this->submission((int)$student['id'],$challenge,'approved',2);
        $milestone=participant_dashboard((int)$student['id'])['milestone'];
        self::assertSame($next,(int)$milestone['badge']['id']);
        self::assertSame(2,$milestone['rules'][0]['progress']);
        self::assertSame(5,(int)$milestone['rules'][0]['required_count']);
        self::assertNotNull(one('SELECT * FROM user_achievements WHERE user_id=? AND achievement_id=?',[$student['id'],$earned]));
    }

    public function testCompletedChallengeMetricDoesNotCountRepeatEvidenceAsExtraChallenges(): void
    {
        $student=$this->account();
        $other=$this->account();
        $challenge=$this->challenge();
        $second=$this->challenge();
        $this->submission((int)$student['id'],$challenge,'approved');
        $this->submission((int)$student['id'],$challenge,'approved');
        $this->submission((int)$student['id'],$second,'pending');
        $this->submission((int)$other['id'],$second,'approved');
        $this->setContext($student);
        self::assertSame(1,(int)metric('Challenges completed'));
    }
}
