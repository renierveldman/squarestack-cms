<?php

class Slug
{
    public static function generate(string $text): string
    {
        if (function_exists('mb_strtolower')) {
            $text = mb_strtolower($text, 'UTF-8');
        } else {
            $text = strtolower($text);
        }

        if (function_exists('iconv')) {
            $converted = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
            if ($converted !== false) {
                $text = $converted;
            }
        }

        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        $text = trim($text, '-');
        $text = preg_replace('/-{2,}/', '-', $text);

        return $text;
    }

    public static function unique(string $base, string $table, string $column = 'slug', int $excludeId = 0): string
    {
        $db      = Database::getInstance();
        $slug    = $base;
        $counter = 1;

        while (true) {
            if ($excludeId > 0) {
                $row = $db->fetch(
                    "SELECT COUNT(*) AS cnt FROM `{$table}` WHERE `{$column}` = ? AND `id` != ?",
                    [$slug, $excludeId]
                );
            } else {
                $row = $db->fetch(
                    "SELECT COUNT(*) AS cnt FROM `{$table}` WHERE `{$column}` = ?",
                    [$slug]
                );
            }

            if ((int)($row['cnt'] ?? 0) === 0) {
                break;
            }

            $counter++;
            $slug = $base . '-' . $counter;
        }

        return $slug;
    }
}
