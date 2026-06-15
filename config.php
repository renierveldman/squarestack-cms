<?php
$_host = $_SERVER['HTTP_HOST'] ?? 'localhost';

if ($_host === 'localhost' || str_starts_with($_host, 'localhost:')) {
    // Local MAMP
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'squarestackcms');
    define('DB_USER', 'root');
    define('DB_PASS', 'root');
    define('SITE_URL', 'http://' . $_host . '/squarestack-cms');
} else {
    // Production server
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'squarestackcms');
    define('DB_USER', 'root');
    define('DB_PASS', 'root');
    define('SITE_URL', 'https://' . $_host);
}
define('THEME', 'starter');
define('CACHE_ENABLED', true);
define('CACHE_TTL', 3600);
define('UPLOAD_MAX_SIZE', 10 * 1024 * 1024); // 10MB
define('WEBP_QUALITY', 85);
define('ROOT_PATH', __DIR__);
define('CORE_PATH', ROOT_PATH . '/core');
define('THEME_PATH', ROOT_PATH . '/themes/' . THEME);
define('UPLOAD_PATH', ROOT_PATH . '/uploads');
define('CACHE_PATH', ROOT_PATH . '/cache');
define('ADMIN_PATH', ROOT_PATH . '/admin');
