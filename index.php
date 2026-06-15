<?php
require_once __DIR__ . '/config.php';
require_once CORE_PATH . '/Database.php';
require_once CORE_PATH . '/Cache.php';
require_once CORE_PATH . '/Settings.php';
require_once CORE_PATH . '/Slug.php';
require_once CORE_PATH . '/CMS.php';
require_once CORE_PATH . '/Auth.php';
require_once CORE_PATH . '/Media.php';
require_once CORE_PATH . '/Router.php';
require_once CORE_PATH . '/helpers.php';

// Bootstrap theme functions
$themeFunctions = THEME_PATH . '/functions.php';
if (file_exists($themeFunctions)) {
    require_once $themeFunctions;
}

$router = new Router();
$router->dispatch();
