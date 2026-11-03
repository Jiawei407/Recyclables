<?php

use Recyclables\Tests\Support\HttpTestCase;

final class AchievementPersistenceTest extends HttpTestCase
{
    private function badge(): int
    {
        query("INSERT INTO achievements (name,status) VALUES ('Retained fixture badge','active')");
        $id=(int)mysqli_insert_id($GLOBALS['conn']);
        query("INSERT INTO achievement_rules (achievement_id,required_count,item,condition_name) VALUES (?,1,'Plastic bottles','Exactly')",[$id]);
        return $id;
    }

    private function raiseThreshold(int $id): void
    {
        $this->loginAs($this->user('admin'));
        $response=$this->postAction('/admin/achievements-edit.php?id=' . $id,[
            'action'=>'save-achievement','id'=>$id,'achievement-name'=>'Retained fixture badge',
            'achievement-status'=>'active','requirement-1-count'=>'999',
            'requirement-1-item'=>'Plastic bottles','requirement-1-condition'=>'At least',
        ]);
        self::assertSame(303,$response['status'],$response['body']);
        self::assertSame(999,(int)one('SELECT required_count FROM achievement_rules WHERE achievement_id=?',[$id])['required_count']);
    }

    public function testApprovalPersistsEarnedBadgeBeforeParticipantViewsProgressAndBeforeRuleEdit(): void
    {
        $student=$this->user();
        $challenge=$this->challenge();
        $badge=$this->badge();
        $submission=$this->submission((int)$student['id'],(int)$challenge['id']);
        $this->loginAs($this->user('coordinator'));
        self::assertNull(one('SELECT * FROM user_achievements WHERE user_id=? AND achievement_id=?',[$student['id'],$badge]));
        $response=$this->postAction('/coordinator/submissions-review-submission.php',[
            'action'=>'review','id'=>$submission['id'],'decision'=>'approved','review-notes'=>'',
            'review-matches'=>'1','review-count'=>'1','review-duplicate'=>'1',
        ]);
        self::assertSame(303,$response['status'],$response['body']);
        // Query the row directly: no participant home/achievement GET has caused the award.
        self::assertNotNull(one('SELECT * FROM user_achievements WHERE user_id=? AND achievement_id=?',[$student['id'],$badge]));
        $this->raiseThreshold($badge);
        self::assertTrue(achievement_progress($badge,(int)$student['id'])['earned']);
        self::assertSame(1,(int)one('SELECT COUNT(*) AS total FROM user_achievements WHERE user_id=? AND achievement_id=?',[$student['id'],$badge])['total']);
    }

    public function testEditingOldActiveRulesPreservesUnviewedLegacyQualificationOnlyForQualifiedStudents(): void
    {
        $qualified=$this->user();
        $unqualified=$this->user();
        $challenge=$this->challenge();
        $badge=$this->badge();
        $this->submission((int)$qualified['id'],(int)$challenge['id'],['status'=>'approved']);
        self::assertSame(0,(int)one('SELECT COUNT(*) AS total FROM user_achievements')['total']);
        $this->raiseThreshold($badge);
        self::assertNotNull(one('SELECT * FROM user_achievements WHERE user_id=? AND achievement_id=?',[$qualified['id'],$badge]));
        self::assertNull(one('SELECT * FROM user_achievements WHERE user_id=? AND achievement_id=?',[$unqualified['id'],$badge]));
        self::assertTrue(achievement_progress($badge,(int)$qualified['id'])['earned']);
        self::assertFalse(achievement_progress($badge,(int)$unqualified['id'])['earned']);
    }
}
