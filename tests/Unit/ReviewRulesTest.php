<?php

use PHPUnit\Framework\TestCase;

final class ReviewRulesTest extends TestCase
{
    /** @dataProvider statusTransitions */
    public function testReviewTransitionsFollowThePendingOnlyPolicy($oldStatus, $newStatus, bool $allowed): void
    {
        self::assertSame($allowed, can_review($oldStatus, $newStatus));
    }

    public static function statusTransitions(): array
    {
        $cases = [];
        $statuses = ['pending', 'approved', 'rejected', 'needs-revision'];
        $allowedFromPending = ['approved', 'rejected', 'needs-revision'];
        foreach ($statuses as $old) {
            foreach ($statuses as $new) {
                $cases[$old . ' to ' . $new] = [
                    $old, $new, $old === 'pending' && in_array($new, $allowedFromPending, true),
                ];
            }
        }
        return $cases;
    }

    /** @dataProvider invalidStatuses */
    public function testUnknownOrWrongTypeStatusesCannotBeReviewed($status): void
    {
        self::assertFalse(can_review($status, 'approved'));
        self::assertFalse(can_review('pending', $status));
    }

    public static function invalidStatuses(): array
    {
        return ['empty' => [''], 'unknown' => ['deleted'], 'wrong case' => ['Approved'],
            'wrong separator' => ['needs_revision'], 'surrounding whitespace' => [' approved '],
            'null' => [null], 'integer' => [0], 'array' => [['approved']]];
    }

    public function testFinalDecisionsCannotBeReversedOrRepeated(): void
    {
        foreach (['approved', 'rejected', 'needs-revision'] as $decided) {
            foreach (['pending', 'approved', 'rejected', 'needs-revision'] as $requested) {
                self::assertFalse(can_review($decided, $requested), "$decided cannot become $requested");
            }
        }
    }
}
