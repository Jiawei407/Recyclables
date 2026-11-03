<?php

require_once dirname(__DIR__) . '/Support/DatabaseTestCase.php';

final class AchievementProgressTest extends DatabaseTestCase
{
    public function testBadgeWithoutRequirementsOrExplicitAwardIsNotEarned(): void
    {
        $user = $this->account();
        $this->assertFalse(achievement_progress($this->badge(), (int) $user['id'])['earned']);
    }

    public function testQuantityRequirementsUseOnlyApprovedMatchingEvidenceOwnedByUser(): void
    {
        $user = $this->account();
        $other = $this->account();
        $bottles = $this->challenge();
        $cups = $this->challenge(['tracked_item' => 'Reusable cup']);
        $this->submission((int) $user['id'], $bottles, 'approved', 3);
        $this->submission((int) $user['id'], $bottles, 'approved', 2);
        $this->submission((int) $user['id'], $bottles, 'pending', 100);
        $this->submission((int) $user['id'], $bottles, 'rejected', 100);
        $this->submission((int) $user['id'], $bottles, 'needs-revision', 100);
        $this->submission((int) $other['id'], $bottles, 'approved', 100);
        $this->submission((int) $user['id'], $cups, 'approved', 100);
        $badge = $this->badge([[5, 'Plastic bottles', 'At least']]);
        $result = achievement_progress($badge, (int) $user['id']);
        $this->assertTrue($result['earned']);
        $this->assertSame(5, $result['rules'][0]['progress']);
    }

    /** @dataProvider cupThresholds */
    public function testAtLeastThresholdAllowsExactAndAboveTarget(int $quantity, bool $expected): void
    {
        $user = $this->account();
        $cups = $this->challenge(['tracked_item' => 'Reusable cup']);
        $this->submission((int) $user['id'], $cups, 'approved', $quantity);
        $badge = $this->badge([[5, 'Reusable cup', 'At least']]);
        $this->assertSame($expected, achievement_progress($badge, (int) $user['id'])['earned']);
    }

    public static function cupThresholds(): array
    {
        return ['below' => [4, false], 'at' => [5, true], 'above' => [6, true]];
    }

    public function testApprovedActivityRequirementCountsRecordsRatherThanQuantity(): void
    {
        $user = $this->account();
        $challenge = $this->challenge();
        $this->submission((int) $user['id'], $challenge, 'approved', 100);
        $badge = $this->badge([[2, 'Approved activity', 'At least']]);
        $result = achievement_progress($badge, (int) $user['id']);
        $this->assertFalse($result['earned']);
        $this->assertSame(1, $result['rules'][0]['progress']);
    }

    public function testAllRequirementsMustBeMet(): void
    {
        $user = $this->account();
        $bottles = $this->challenge();
        $cleanup = $this->challenge(['tracked_item' => 'Campus cleanup']);
        $badge = $this->badge([[5, 'Plastic bottles', 'At least'], [1, 'Campus cleanup', 'At least']]);
        $this->submission((int) $user['id'], $bottles, 'approved', 5);
        $this->assertFalse(achievement_progress($badge, (int) $user['id'])['earned']);
        $this->submission((int) $user['id'], $cleanup, 'approved', 99);
        $this->assertTrue(achievement_progress($badge, (int) $user['id'])['earned']);
    }

    public function testFacultyAchievementAggregatesOnlyStudentsInThatFaculty(): void
    {
        $representative = $this->account(['role' => 'faculty']);
        $student = $this->account();
        $outsider = $this->account(['faculty' => 'School of Engineering']);
        $bottles = $this->challenge();
        $this->submission((int) $student['id'], $bottles, 'approved', 3);
        $this->submission((int) $representative['id'], $bottles, 'approved', 100);
        $this->submission((int) $outsider['id'], $bottles, 'approved', 100);
        $badge = $this->badge([[5, 'Plastic bottles', 'At least']]);
        $result = achievement_progress($badge, (int) $representative['id'], 'School of Computing');
        $this->assertFalse($result['earned']);
        $this->assertSame(3, $result['rules'][0]['progress']);
    }

    public function testExplicitCampaignAwardDoesNotRequireCurrentRuleQualification(): void
    {
        $student = $this->account();
        $badge = $this->badge([[999, 'Plastic bottles', 'At least']]);
        query('INSERT INTO user_achievements (user_id,achievement_id) VALUES (?,?)', [$student['id'], $badge]);
        $this->assertTrue(achievement_progress($badge, (int) $student['id'])['earned']);
        $this->assertFalse(achievement_progress($badge, (int) $student['id'], 'School of Computing')['earned']);
    }
}
