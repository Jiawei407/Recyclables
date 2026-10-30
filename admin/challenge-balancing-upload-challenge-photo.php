<?php
$page_role='admin'; require __DIR__ . '/../includes/bootstrap.php';
redirect('admin/challenge-balancing.php' . (isset($_GET['id']) ? '?id=' . (int)$_GET['id'] : ''));
