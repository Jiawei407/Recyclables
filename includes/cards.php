<?php
$card_rows=page_records('challenges');
if (!$card_rows) echo '<article class="media-card"><h3>No challenges available.</h3><p>Challenges appear here when published.</p></article>';
foreach ($card_rows as $challenge) {
    $featured= $user && $role === 'faculty' ? one('SELECT * FROM faculty_promotions WHERE faculty=? AND challenge_id=?',[$faculty,$challenge['id']]) : null;
?>
<article class="media-card" data-record data-category="<?= e($challenge['category']) ?>">
  <?php if ($challenge['image'] && $user): ?>
  <img src="<?= e($base) ?>/file.php?type=challenge&amp;id=<?= (int)$challenge['id'] ?>" alt="<?= e($challenge['title']) ?>" width="600" height="400" loading="lazy">
  <?php endif; ?>
  <span class="category-tag"><?= e($challenge['category']) ?></span>
  <h3><?= e($challenge['title']) ?></h3>
  <p><?= e($challenge['points']) ?> points · <?= e($challenge['end_date']) ?></p>
  <p><?= e($challenge['description']) ?></p>
  <p class="field-hint"><?= e($challenge['completion_requirement']) ?></p>
  <?php if ($role === 'faculty'): ?>
  <form method="post">
    <?= csrf_field() ?><input type="hidden" name="action" value="promote">
    <input type="hidden" name="id" value="<?= (int)$challenge['id'] ?>">
    <input type="hidden" name="remove" value="<?= $featured ? '1' : '0' ?>">
    <button type="submit" class="button button--outline"><?= $featured ? 'Remove feature' : 'Feature for faculty' ?></button>
  </form>
  <?php elseif ($role === 'user'): ?>
    <?php if (one('SELECT * FROM user_challenges WHERE user_id=? AND challenge_id=?',[$uid,$challenge['id']])): ?>
    <a class="button button--outline" href="<?= e($base) ?>/user/submit-evidence-new-submission.php?challenge_id=<?= (int)$challenge['id'] ?>">Submit evidence</a>
    <?php else: ?>
    <form method="post">
      <?= csrf_field() ?><input type="hidden" name="action" value="join"><input type="hidden" name="id" value="<?= (int)$challenge['id'] ?>">
      <button type="submit" class="button button--primary">Join challenge</button>
    </form>
    <?php endif; ?>
  <?php endif; ?>
</article>
<?php } ?>
