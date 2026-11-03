<?php

require_once dirname(__DIR__) . '/Support/DatabaseTestCase.php';

final class RankingsTest extends DatabaseTestCase
{
    public function testRankingUsesLedgerIncludingDeductionsAndZeroBalance(): void
    {
        $first = $this->account();
        $second = $this->account();
        $zero = $this->account();
        $this->points((int) $first['id'], 240);
        $this->points((int) $first['id'], -20);
        $this->points((int) $second['id'], 30);
        $rankings = page_records('rankings');
        $this->assertSame([(int) $first['id'], (int) $second['id'], (int) $zero['id']], $this->ids($rankings));
        $this->assertSame([220, 30, 0], array_map('intval', array_column($rankings, 'points')));
    }

    public function testRankingExcludesSuspendedInactiveAndStaffAccounts(): void
    {
        $participant = $this->account();
        foreach ([['status' => 'suspended'], ['status' => 'inactive'], ['role' => 'faculty'], ['role' => 'coordinator'], ['role' => 'admin']] as $settings) {
            $hidden = $this->account($settings);
            $this->points((int) $hidden['id'], 999);
        }
        $this->assertSame([(int) $participant['id']], $this->ids(page_records('rankings')));
    }

    public function testFacultyRankingSumsOnlyActiveParticipantsInEachFaculty(): void
    {
        $a = $this->account();
        $b = $this->account();
        $c = $this->account(['faculty' => 'School of Engineering']);
        $staff = $this->account(['role' => 'faculty']);
        $suspended = $this->account(['status' => 'suspended']);
        foreach ([[$a, 20], [$b, 30], [$c, 40], [$staff, 999], [$suspended, 999]] as [$person, $amount]) {
            $this->points((int) $person['id'], $amount);
        }
        $ranking = page_records('faculty-rankings');
        $this->assertSame(['School of Computing', 'School of Engineering'], array_column($ranking, 'faculty'));
        $this->assertSame([50, 40], array_map('intval', array_column($ranking, 'points')));
    }

    public function testUnknownRecordKindIsAnEmptyList(): void
    {
        $this->assertSame([], page_records('nonexistent-kind'));
    }
}
