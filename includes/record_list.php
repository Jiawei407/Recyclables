<?php
$list_records=page_records($list_kind);
if (!$list_records) echo '<p class="empty-state">No records available.</p>';
foreach ($list_records as $item) {
    $title=$item['title'] ?? $item['activity'] ?? $item['action'] ?? '';
    $detail=$item['message'] ?? $item['outcome'] ?? $item['details'] ?? '';
    $date=$item['publish_date'] ?? $item['created_at'] ?? '';
    $link='';
    if ($list_kind === 'audit') $link='admin/audit-log-audit-details.php';
    if ($list_kind === 'reports') $link='admin/reports-faculty-report.php';
?>
<article class="panel stack" data-record>
  <h3><?= e($title) ?></h3><p><?= e($detail) ?></p>
  <p class="muted"><?= e($date) ?> <?= e($item['faculty'] ?? '') ?></p>
  <?php if ($link): ?><a class="text-link" href="<?= e($base . '/' . $link) ?>?id=<?= (int)$item['id'] ?>">View details</a><?php endif; ?>
</article>
<?php } ?>
