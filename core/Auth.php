<?php

class Auth
{
    private const MAX_ATTEMPTS = 5;
    private const LOCKOUT_SECS = 15 * 60; // 15 minutes

    private static function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'lifetime' => 0,
                'path'     => '/',
                'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
                'httponly' => true,
                'samesite' => 'Strict',
            ]);
            ini_set('session.use_strict_mode', '1');
            ini_set('session.use_only_cookies', '1');
            session_start();
        }
    }

    private static function clientIp(): string
    {
        foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $key) {
            if (!empty($_SERVER[$key])) {
                return trim(explode(',', $_SERVER[$key])[0]);
            }
        }
        return '0.0.0.0';
    }

    public static function isLockedOut(string $email): bool
    {
        $ip     = self::clientIp();
        $cutoff = date('Y-m-d H:i:s', time() - self::LOCKOUT_SECS);
        $db     = Database::getInstance();

        $row = $db->fetch(
            'SELECT COUNT(*) AS cnt FROM login_attempts WHERE ip_address = ? AND attempted_at > ?',
            [$ip, $cutoff]
        );
        if ($row && (int) $row['cnt'] >= self::MAX_ATTEMPTS) {
            return true;
        }

        if ($email !== '') {
            $row = $db->fetch(
                'SELECT COUNT(*) AS cnt FROM login_attempts WHERE email = ? AND attempted_at > ?',
                [strtolower(trim($email)), $cutoff]
            );
            if ($row && (int) $row['cnt'] >= self::MAX_ATTEMPTS) {
                return true;
            }
        }

        return false;
    }

    public static function lockoutSecondsRemaining(string $email): int
    {
        $ip     = self::clientIp();
        $cutoff = date('Y-m-d H:i:s', time() - self::LOCKOUT_SECS);
        $db     = Database::getInstance();

        $row = $db->fetch(
            'SELECT MIN(attempted_at) AS oldest FROM login_attempts
             WHERE (ip_address = ? OR email = ?) AND attempted_at > ?',
            [$ip, strtolower(trim($email)), $cutoff]
        );

        if (!$row || !$row['oldest']) {
            return 0;
        }

        return max(0, (strtotime($row['oldest']) + self::LOCKOUT_SECS) - time());
    }

    private static function recordFailedAttempt(string $email): void
    {
        $db = Database::getInstance();
        $db->query(
            'INSERT INTO login_attempts (ip_address, email) VALUES (?, ?)',
            [self::clientIp(), strtolower(trim($email))]
        );
    }

    private static function clearAttempts(string $email): void
    {
        $db = Database::getInstance();
        $db->query(
            'DELETE FROM login_attempts WHERE ip_address = ? OR email = ?',
            [self::clientIp(), strtolower(trim($email))]
        );
    }

    public static function login(string $email, string $password): bool
    {
        self::startSession();

        if (self::isLockedOut($email)) {
            return false;
        }

        $db   = Database::getInstance();
        $user = $db->fetch('SELECT id, name, email, role, avatar, password FROM users WHERE email = ? LIMIT 1', [$email]);

        if ($user === false || !password_verify($password, $user['password'])) {
            self::recordFailedAttempt($email);
            return false;
        }

        self::clearAttempts($email);
        session_regenerate_id(true);

        $_SESSION['user'] = [
            'id'     => $user['id'],
            'name'   => $user['name'],
            'email'  => $user['email'],
            'role'   => $user['role'],
            'avatar' => $user['avatar'] ?? null,
        ];

        return true;
    }

    public static function logout(): void
    {
        self::startSession();

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();

        header('Location: ' . SITE_URL . '/admin/login.php');
        exit;
    }

    public static function check(): array|false
    {
        self::startSession();

        if (!empty($_SESSION['user']) && is_array($_SESSION['user'])) {
            return $_SESSION['user'];
        }

        return false;
    }

    public static function require(): void
    {
        if (self::check() === false) {
            header('Location: ' . SITE_URL . '/admin/login.php');
            exit;
        }
    }

    public static function generateCsrf(): string
    {
        self::startSession();

        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;

        return $token;
    }

    public static function verifyCsrf(string $token): bool
    {
        self::startSession();

        if (empty($_SESSION['csrf_token'])) {
            return false;
        }

        return hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function currentUser(): array|false
    {
        return self::check();
    }
}
