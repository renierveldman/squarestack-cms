<?php

namespace Core;

class Slug
{
    public static function generate(string $text): string
    {
        // Transliterate UTF-8 to ASCII
        if (function_exists('mb_strtolower')) {
            $text = mb_strtolower($text, 'UTF-8');
        } else {
            $text = strtolower($text);
        }

        // Try iconv transliteration to handle accented characters
        if (function_exists('iconv')) {
            $converted = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
            if ($converted !== false) {
                $text = $converted;
            }
        }

        // Replace non-alphanumeric characters with hyphens
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);

        // Trim leading and trailing hyphens
        $text = trim($text, '-');

        // Collapse multiple consecutive hyphens
        $text = preg_replace('/-{2,}/', '-', $text);

        return $text;
    }

    public static function unique(string $base, string $table, string $column = 'slug', int $excludeId = 0): string
    {
        $db = \Core\Database::getInstance();

        $slug = $base;
        $counter = 1;

        while (true) {
            if ($excludeId > 0) {
                $stmt = $db->prepare(
                    "SELECT COUNT(*) FROM `{$table}` WHERE `{$column}` = ? AND `id` != ?"
                );
                $stmt->execute([$slug, $excludeId]);
            } else {
                $stmt = $db->prepare(
                    "SELECT COUNT(*) FROM `{$table}` WHERE `{$column}` = ?"
                );
                $stmt->execute([$slug]);
            }

            $count = (int) $stmt->fetchColumn();

            if ($count === 0) {
                break;
            }

            $counter++;
            $slug = $base . '-' . $counter;
        }

        return $slug;
    }
}
