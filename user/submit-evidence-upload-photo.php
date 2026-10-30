<?php
$page_role='user'; require __DIR__ . '/../includes/bootstrap.php';
redirect('user/submit-evidence-new-submission.php' . (isset($_GET['id']) ? '?id=' . (int)$_GET['id'] : ''));
