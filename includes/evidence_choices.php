<?php
$choices=rows("SELECT c.* FROM challenges c JOIN user_challenges uc ON uc.challenge_id=c.id WHERE uc.user_id=? AND c.status='open' AND c.start_date<=CURDATE() AND c.end_date>=CURDATE() ORDER BY c.title",[$uid]);
if (!$choices) echo '<p class="field-hint">Join an open challenge before submitting evidence.</p>';
foreach ($choices as $choice) {
    $chosen=(int)($_POST['challenge-id'] ?? $_GET['challenge_id'] ?? 0) === (int)$choice['id'];
?>
<label class="check-option">
  <input form="backend-form" type="radio" name="challenge-id" value="<?= (int)$choice['id'] ?>" required <?= $chosen ? 'checked' : '' ?>>
  <span><?= e($choice['title']) ?> · <?= e($choice['points']) ?> points</span>
</label>
<?php } ?>
