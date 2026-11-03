<?php
use Recyclables\Tests\Support\HttpTestCase;

final class AccountDeletionIsolationTest extends HttpTestCase
{
    public function testPermanentDeletionPurgesOnlyTargetsPrivateRecordsAndKeepsSharedHistory(): void
    {
        $target = $this->user(); $other = $this->user(); $admin = $this->user('admin');
        $challenge = $this->challenge();
        query('UPDATE challenges SET created_by=? WHERE id=?',[$target['id'],$challenge['id']]);
        $targetPhoto = bin2hex(random_bytes(16)) . '.png'; $otherPhoto = bin2hex(random_bytes(16)) . '.png';
        $folder = dirname(__DIR__, 2) . '/storage/uploads';
        if (!is_dir($folder)) mkdir($folder,0755,true);
        file_put_contents($folder . '/' . $targetPhoto, 'private target fixture');
        file_put_contents($folder . '/' . $otherPhoto, 'private other fixture');
        $targetSubmission = $this->submission((int)$target['id'], (int)$challenge['id'], ['photo'=>$targetPhoto]);
        $otherSubmission = $this->submission((int)$other['id'], (int)$challenge['id'], ['photo'=>$otherPhoto]);
        query('INSERT INTO point_transactions (user_id,submission_id,points,reason) VALUES (?,?,30,?)',[$target['id'],$targetSubmission['id'],'Target points']);
        query('INSERT INTO point_transactions (user_id,submission_id,points,reason) VALUES (?,?,30,?)',[$other['id'],$otherSubmission['id'],'Other points']);
        query('INSERT INTO audit_logs (actor_id,action,details) VALUES (?,?,?)',[$target['id'],'fixture-history','Preserve this audit entry']);
        $auditId = mysqli_insert_id($GLOBALS['conn']);
        $this->loginAs($admin);
        $response = $this->postAction('/admin/users-delete-user.php',['action'=>'delete-user','id'=>$target['id']]);
        self::assertSame(303,$response['status']);
        self::assertNull(one('SELECT id FROM users WHERE id=?',[$target['id']]));
        self::assertNull(one('SELECT id FROM submissions WHERE id=?',[$targetSubmission['id']]));
        self::assertSame(30,(int)one('SELECT points FROM point_transactions WHERE user_id=?',[$other['id']])['points']);
        self::assertNotNull(one('SELECT id FROM submissions WHERE id=?',[$otherSubmission['id']]));
        self::assertNull(one('SELECT created_by FROM challenges WHERE id=?',[$challenge['id']])['created_by']);
        $audit = one('SELECT actor_id,details FROM audit_logs WHERE id=?',[$auditId]);
        self::assertNull($audit['actor_id']); self::assertSame('Preserve this audit entry',$audit['details']);
        self::assertFileDoesNotExist($folder . '/' . $targetPhoto); self::assertFileExists($folder . '/' . $otherPhoto);
    }
}
