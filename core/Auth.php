<?php

class Auth
{
    private static function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function login(string $email, string $password): bool
    {
        self::startSession();

        $db = Database::getInstance();
        $user = $db->fetch('SELECT id, name, email, role, avatar, password FROM users WHERE email = ? LIMIT 1', [$email]);

        if ($user === false) {
            return false;
        }

        if (!password_verify($password, $user['password'])) {
            return false;
        }

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
