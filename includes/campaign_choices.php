<?php
foreach (rows('SELECT id,title FROM challenges ORDER BY title') as $challenge) {
    $checked=isset($_POST['campaign-challenges']) ? in_array($challenge['id'],(array)$_POST['campaign-challenges']) : ($id && one('SELECT * FROM campaign_challenges WHERE campaign_id=? AND challenge_id=?',[$id,$challenge['id']]));
?>
<label class="check-option"><input form="backend-form" type="checkbox" name="campaign-challenges[]" value="<?= (int)$challenge['id'] ?>" <?= $checked ? 'checked' : '' ?>><span><?= e($challenge['title']) ?></span></label>
<?php } ?>
