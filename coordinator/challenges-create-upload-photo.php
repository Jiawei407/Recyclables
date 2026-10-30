<?php
$page_role='coordinator'; require __DIR__ . '/../includes/bootstrap.php';
redirect('coordinator/challenges-create.php' . (isset($_GET['id']) ? '?id=' . (int)$_GET['id'] : ''));
