<?php

class Cache
{
    private static function sanitizeUri(string $uri): string
    {
        $uri = trim($uri, '/');
        $uri = str_replace('/', '-', $uri);
        return '/' . $uri;
    }

    private static function cachePath(string $uri): string
    {
        $sanitized = self::sanitizeUri($uri);
        return rtrim(CACHE_PATH, '/') . '/pages' . $sanitized . '/index.html';
    }

    public static function get(string $uri): string|false
    {
        $path = self::cachePath($uri);
        if (!file_exists($path)) {
            return false;
        }
        return file_get_contents($path);
    }

    public static function set(string $uri, string $html): void
    {
        $path = self::cachePath($uri);
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($path, $html);
        file_put_contents($path . '.gz', gzencode($html));
    }

    public static function flush(): void
    {
        $pagesDir = rtrim(CACHE_PATH, '/') . '/pages';
        if (!is_dir($pagesDir)) {
            return;
        }
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($pagesDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($iterator as $item) {
            if ($item->isDir()) {
                rmdir($item->getRealPath());
            } else {
                unlink($item->getRealPath());
            }
        }
    }

    public static function flushPage(string $uri): void
    {
        $path = self::cachePath($uri);
        if (file_exists($path)) {
            unlink($path);
        }
        if (file_exists($path . '.gz')) {
            unlink($path . '.gz');
        }
    }

    public static function isEnabled(): bool
    {
        return defined('CACHE_ENABLED') && CACHE_ENABLED;
    }

    public static function shouldCache(): bool
    {
        if (!self::isEnabled()) {
            return false;
        }
        if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'GET') {
            return false;
        }
        if (!empty($_SERVER['QUERY_STRING'])) {
            return false;
        }
        if (isset($_COOKIE['squarestack_admin'])) {
            return false;
        }
        return true;
    }
}
