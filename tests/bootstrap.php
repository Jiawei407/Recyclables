<?php
// Deliberately avoid includes/bootstrap.php: unit tests need no session or database.
date_default_timezone_set('Asia/Kuala_Lumpur');
require_once dirname(__DIR__) . '/includes/rules.php';
require_once dirname(__DIR__) . '/includes/functions.php';

spl_autoload_register(static function ($class) {
    if (strpos($class, 'Recyclables\\Tests\\Support\\') === 0) {
        $file = __DIR__ . '/Support/' . substr($class, strlen('Recyclables\\Tests\\Support\\')) . '.php';
        if (is_file($file)) require_once $file;
    }
});
