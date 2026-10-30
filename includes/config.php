<?php
// XAMPP defaults. Change these four values if your MySQL setup differs.
$db_host = getenv('RECYCLABLES_DB_HOST') ?: '127.0.0.1';
$db_port = (int)(getenv('RECYCLABLES_DB_PORT') ?: 3306);
$db_name = getenv('RECYCLABLES_DB_NAME') ?: 'recyclables_db';
$db_user = getenv('RECYCLABLES_DB_USER') ?: 'root';
$db_password = getenv('RECYCLABLES_DB_PASSWORD') ?: '';
