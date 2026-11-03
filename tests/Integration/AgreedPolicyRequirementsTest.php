<?php

require_once dirname(__DIR__) . '/Support/DatabaseTestCase.php';

/** @group requirements */
final class AgreedPolicyRequirementsTest extends DatabaseTestCase
{
    public function testOlderAccountWinsAnIndividualPointTieEvenWhenItsNameSortsLast(): void
    {
        $older = $this->account(['full_name' => 'Zara Older', 'created_at' => '2024-01-01 00:00:00']);
        $newer = $this->account(['full_name' => 'Adam Newer', 'created_at' => '2025-01-01 00:00:00']);
        $this->points((int) $older['id'], 100);
        $this->points((int) $newer['id'], 100);
        $this->assertSame([(int) $older['id'], (int) $newer['id']], $this->ids(page_records('rankings')),
            'Agreed policy: equal Green Points rank the older account first, not alphabetical name.');
    }

    public function testEarnedExactlyBadgeIsMaintainedWhenApprovedQuantityLaterIncreases(): void
    {
        $student = $this->account();
        $challenge = $this->challenge();
        $badge = $this->badge([[5, 'Plastic bottles', 'Exactly']]);
        $this->submission((int) $student['id'], $challenge, 'approved', 5);
        $this->assertTrue(achievement_progress($badge, (int) $student['id'])['earned']);
        $this->submission((int) $student['id'], $challenge, 'approved', 1);
        $this->assertTrue(achievement_progress($badge, (int) $student['id'])['earned'],
            'Agreed policy: previously earned badges remain until explicitly removed by an administrator.');
    }

    public function testEarnedBadgeIsMaintainedWhenAdministratorRaisesRequirement(): void
    {
        $student = $this->account();
        $challenge = $this->challenge();
        $badge = $this->badge([[5, 'Plastic bottles', 'At least']]);
        $this->submission((int) $student['id'], $challenge, 'approved', 5);
        $this->assertTrue(achievement_progress($badge, (int) $student['id'])['earned']);
        query('UPDATE achievement_rules SET required_count=10 WHERE achievement_id=?', [$badge]);
        $this->assertTrue(achievement_progress($badge, (int) $student['id'])['earned'],
            'Changing badge requirements is not an explicit removal of an earned badge.');
    }
}
