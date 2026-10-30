<?php
// Optional router only for PHP's built-in development server.
$path=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
if (preg_match('~^/(storage|includes|actions|database)(/|$)~i',$path)) {
    http_response_code(403); exit('Access denied.');
}
return false;
