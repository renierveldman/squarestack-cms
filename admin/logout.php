<?php
require_once dirname(__DIR__) . '/config.php';
require_once CORE_PATH . '/Database.php';
require_once CORE_PATH . '/Auth.php';
session_start();
session_destroy();
setcookie('squarestack_admin', '', time() - 3600, '/', '', false, true);
header('Location: ' . SITE_URL . '/admin/login.php');
exit;
