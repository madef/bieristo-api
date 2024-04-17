<?php

define('DOMAIN', getenv('DOMAIN'));
define('DOMAIN_PREFIX', getenv('DOMAIN_PREFIX'));
define('API_DOMAIN', getenv('API_DOMAIN'));
define('API_PREFIX', getenv('API_PREFIX'));
define('DEV_MOD', (bool) getenv('DEV_MOD'));
define('ADMIN_DOMAIN', getenv('ADMIN_DOMAIN'));
define('ADMIN_PREFIX', getenv('ADMIN_PREFIX'));
define('MONGO_HOST', getenv('MONGO_HOST'));
define('MONGO_DBNAME', getenv('MONGO_DBNAME'));
define('MONGO_USER', getenv('MONGO_USER'));
define('MONGO_PASSWORD', getenv('MONGO_PASSWORD'));
define('MONGO_PORT', getenv('MONGO_PORT'));
define('SMTP_HOST', getenv('SMTP_HOST'));
define('SMTP_PORT', (int) getenv('SMTP_PORT'));
define('SMTP_PROTOCOL', getenv('SMTP_PROTOCOL')); // tcp || ssl
define('SMTP_USERNAME', getenv('SMTP_USERNAME'));
define('SMTP_PASSWORD', getenv('SMTP_PASSWORD'));
define('SMTP_SENDER_EMAIL', getenv('SMTP_SENDER_EMAIL'));
define('SMTP_SENDER_LABEL', getenv('SMTP_SENDER_LABEL'));

if (isset($_SERVER['HTTP_ORIGIN'])) {
    $httpOrigin = $_SERVER['HTTP_ORIGIN'];
    if ($httpOrigin === getenv('DOMAIN_PREFIX') . getenv('DOMAIN')) {
        header('Access-Control-Allow-Origin: ' . getenv('DOMAIN_PREFIX') . getenv('DOMAIN'));
    } else if ($httpOrigin === getenv('ADMIN_PREFIX') . getenv('ADMIN_DOMAIN')) {
        header('Access-Control-Allow-Origin: ' . getenv('ADMIN_PREFIX') . getenv('ADMIN_DOMAIN'));
    }
}
