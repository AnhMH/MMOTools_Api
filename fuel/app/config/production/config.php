<?php
$envConf = array(
    'img_url' => 'https://img.hoanganhonline.com/',
    'send_email' => true,
    'test_email' => '', // will send to this email for testing 
    'api_check_security' => true,
    'api_request_minute' => 24 * 60,
    'accesstrade' => array(
        'access_key' => 'GKbYMT1dQsIJ4bcwwG_FJ2s_3XPYX0BZ',
        'secret_key' => '@=ajq7302o=6ogrlf-#e8cjxrryb%)og'
    )
);

if (isset($_SERVER['SERVER_NAME'])) {
    if (file_exists(__DIR__ . DIRECTORY_SEPARATOR . $_SERVER['SERVER_NAME'] . '.php')) {
        include_once (__DIR__ . DIRECTORY_SEPARATOR . $_SERVER['SERVER_NAME'] . '.php');
        $envConf = array_merge($envConf, $domainConf);
    }
}
if (file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'db.read.php')) {
    include_once (__DIR__ . DIRECTORY_SEPARATOR . 'db.read.php');
    $envConf = array_merge($envConf, $dbReadConf);
}
return $envConf;