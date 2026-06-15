<?php

class Settings
{
    private static array $cache = [];

    public static function get(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, self::$cache)) {
            return self::$cache[$key];
        }

        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT setting_value FROM settings WHERE setting_key = ? LIMIT 1');
        $stmt->execute([$key]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return $default;
        }

        self::$cache[$key] = $row['setting_value'];

        return self::$cache[$key];
    }

    public static function set(string $key, mixed $value): void
    {
        $db = Database::getInstance();
        $stmt = $db->prepare(
            'INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
        );
        $stmt->execute([$key, $value]);

        self::$cache[$key] = $value;
    }

    public static function all(): array
    {
        $db = Database::getInstance();
        $stmt = $db->query('SELECT setting_key, setting_value FROM settings');
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
