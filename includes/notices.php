<?php if ($error): ?>
<div class="notice notice--info" role="alert" data-popup-message="<?= e($error) ?>"><p><?= e($error) ?></p></div>
<?php endif; ?>
<?php if ($error || $message): ?>
<script src="<?= e($base ?? '') ?>/scripts/feedback.js?v=20261030" defer></script>
<?php endif; ?>
<?php if ($message): ?>
<div class="notice notice--info" role="status" data-popup-message="<?= e($message) ?>"><p><?= e($message) ?></p></div>
<?php endif; ?>
