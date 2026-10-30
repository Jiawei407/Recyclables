<?php
$badges=rows($role === 'admin' ? 'SELECT * FROM achievements ORDER BY id DESC' : "SELECT * FROM achievements WHERE status='active' ORDER BY id DESC");
$shown=0;
$is_faculty=$role === 'faculty';
foreach ($badges as $badge) {
    $progress=achievement_progress($badge['id'],$uid,$is_faculty ? $faculty : null);
    if (str_contains($section,'earned') && !$progress['earned']) continue;
    if (str_contains($section,'locked') && $progress['earned']) continue;
    $shown++;
?>
<article class="badge-card" data-record>
  <?php if ($badge['image']): ?><img src="<?= e($base) ?>/file.php?type=achievement&amp;id=<?= (int)$badge['id'] ?>" alt="" width="80" height="80"><?php endif; ?>
  <h3><?= e($badge['name']) ?></h3>
  <?php if ($is_faculty): ?><p class="field-hint">Collective student progress: <?= e($faculty) ?></p><?php endif; ?>
  <p><?= $role === 'admin' ? e($badge['status']) : ($progress['earned'] ? ($is_faculty ? 'Faculty target met' : 'Earned') : 'In progress') ?></p>
  <?php foreach ($progress['rules'] as $rule): ?>
  <p><?= e($rule['item']) ?>: <?= (int)$rule['progress'] ?> / <?= (int)$rule['required_count'] ?> (<?= e($rule['condition_name']) ?>)</p>
  <?php endforeach; ?>
  <?php if ($role === 'admin'): ?><a class="text-link" href="<?= e($base) ?>/admin/achievements-edit.php?id=<?= (int)$badge['id'] ?>">Edit achievement</a><?php endif; ?>
</article>
<?php }
if (!$shown) echo '<article class="badge-card"><h3>No achievements to display.</h3></article>';
