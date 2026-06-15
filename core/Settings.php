<?php

class Settings
{
    private static array $cache = [];

    public static function get(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, self::$cache)) {
            return self::$cache[$key];
        }

        $db  = Database::getInstance();
        $row = $db->fetch('SELECT setting_value FROM settings WHERE setting_key = ? LIMIT 1', [$key]);

        if ($row === false) {
            return $default;
        }

        self::$cache[$key] = $row['setting_value'];
        return self::$cache[$key];
    }

    public static function set(string $key, mixed $value): void
    {
        $db = Database::getInstance();
        $db->query(
            'INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)',
            [$key, (string)$value]
        );
        self::$cache[$key] = $value;
    }

    public static function all(): array
    {
        $db   = Database::getInstance();
        $rows = $db->fetchAll('SELECT setting_key, setting_value FROM settings');

        foreach ($rows as $row) {
            self::$cache[$row['setting_key']] = $row['setting_value'];
        }

        return self::$cache;
    }

    public static function flush(): void
    {
        self::$cache = [];
    }
}
