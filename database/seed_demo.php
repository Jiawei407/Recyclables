<?php
// Optional CLI setup for a fresh installation; existing account passwords are not overwritten.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__ . '/../includes/config.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn=mysqli_connect($db_host,$db_user,$db_password,$db_name,$db_port);
mysqli_set_charset($conn,'utf8mb4');
$sql=file_get_contents(__DIR__ . '/demo_accounts.sql');
$sql=str_replace('USE recyclables_db;', 'USE `' . str_replace('`','``',$db_name) . '`;', $sql);
mysqli_multi_query($conn,$sql);
do { if ($result=mysqli_store_result($conn)) mysqli_free_result($result); } while(mysqli_more_results($conn) && mysqli_next_result($conn));
echo "Valid demo accounts added if missing. See TEST-ACCOUNTS.md for credentials.\n";
