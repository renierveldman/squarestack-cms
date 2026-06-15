<?php
/**
 * Run once via CLI: php create-admin.php
 * Delete this file after running.
 */
if (PHP_SAPI !== 'cli') {
    die('CLI only.');
}

require_once __DIR__ . '/config.php';
require_once CORE_PATH . '/Database.php';

$name     = 'Admin User';
$email    = 'admin@example.com';
$password = 'changeme123';

$db = Database::getInstance();
$hash = password_hash($password, PASSWORD_BCRYPT);

try {
    $db->insert('users', [
        'name'     => $name,
        'email'    => $email,
        'password' => $hash,
        'role'     => 'admin',
    ]);
    echo "Admin created successfully.\n";
    echo "Email: {$email}\n";
    echo "Password: {$password}\n";
    echo "IMPORTANT: Delete this file now.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
