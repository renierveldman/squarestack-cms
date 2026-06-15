<?php

class Media
{
    private static array $allowedMimes = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'application/pdf',
    ];

    private static array $convertibleMimes = [
        'image/jpeg',
        'image/png',
        'image/gif',
    ];

    public static function upload(array $file): array
    {
        if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Upload error code: ' . ($file['error'] ?? 'unknown'));
        }

        $maxSize = defined('UPLOAD_MAX_SIZE') ? UPLOAD_MAX_SIZE : 10 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            throw new RuntimeException('File exceeds maximum allowed size.');
        }

        $finfo    = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        if (!in_array($mimeType, self::$allowedMimes, true)) {
            throw new RuntimeException('File type not allowed: ' . $mimeType);
        }

        $year      = date('Y');
        $month     = date('m');
        $uploadDir = rtrim(UPLOAD_PATH, '/') . '/' . $year . '/' . $month . '/';

        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
            throw new RuntimeException('Failed to create upload directory.');
        }

        $baseName      = pathinfo($file['name'], PATHINFO_FILENAME);
        $safeBase      = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $baseName);
        $uniqueId      = bin2hex(random_bytes(8));
        $shouldConvert = in_array($mimeType, self::$convertibleMimes, true);

        if ($shouldConvert) {
            $filename = $safeBase . '_' . $uniqueId . '.webp';
        } else {
            $extMap  = ['image/webp' => 'webp', 'application/pdf' => 'pdf'];
            $ext     = $extMap[$mimeType] ?? strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $filename = $safeBase . '_' . $uniqueId . '.' . $ext;
        }

        $destPath = $uploadDir . $filename;

        if ($shouldConvert) {
            $tmpExt  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $tmpPath = $uploadDir . $safeBase . '_' . $uniqueId . '_tmp.' . $tmpExt;
            if (!move_uploaded_file($file['tmp_name'], $tmpPath)) {
                throw new RuntimeException('Failed to move uploaded file.');
            }
            $converted = self::toWebP($tmpPath, $destPath);
            @unlink($tmpPath);
            if (!$converted) {
                // Fall back to storing original
                $filename = $safeBase . '_' . $uniqueId . '.' . $tmpExt;
                $destPath = $uploadDir . $filename;
                rename($tmpPath, $destPath);
                $shouldConvert = false;
            }
        } else {
            if (!move_uploaded_file($file['tmp_name'], $destPath)) {
                throw new RuntimeException('Failed to move uploaded file.');
            }
        }

        [$width, $height] = self::getDimensions($destPath);
        $url = rtrim(SITE_URL, '/') . '/uploads/' . $year . '/' . $month . '/' . $filename;

        $db = Database::getInstance();
        $id = $db->insert('media', [
            'filename'      => $filename,
            'original_name' => $file['name'],
            'mime_type'     => $shouldConvert ? 'image/webp' : $mimeType,
            'file_size'     => (int)filesize($destPath),
            'width'         => $width ?: null,
            'height'        => $height ?: null,
            'alt_text'      => '',
        ]);

        return [
            'id'            => $id,
            'url'           => $url,
            'filename'      => $filename,
            'original_name' => $file['name'],
            'alt_text'      => '',
            'width'         => $width,
            'height'        => $height,
        ];
    }

    public static function getLibrary(int $page = 1, int $perPage = 24): array
    {
        $page    = max(1, $page);
        $perPage = max(1, $perPage);
        $offset  = ($page - 1) * $perPage;

        $db    = Database::getInstance();
        $count = $db->fetch('SELECT COUNT(*) AS cnt FROM media');
        $total = (int)($count['cnt'] ?? 0);
        $pages = $total > 0 ? (int)ceil($total / $perPage) : 1;

        // Cast to int so PDO sends as integer, avoiding LIMIT/OFFSET string binding issues
        $items = $db->fetchAll(
            'SELECT id, filename, original_name, mime_type, file_size, width, height, alt_text, created_at
             FROM media ORDER BY created_at DESC LIMIT ' . $perPage . ' OFFSET ' . $offset
        );

        // Compute URL for each item
        foreach ($items as &$item) {
            $item['url'] = self::urlFromFilename($item['filename']);
        }
        unset($item);

        return compact('items', 'total', 'pages');
    }

    public static function getById(int $id): array|false
    {
        $db  = Database::getInstance();
        $row = $db->fetch(
            'SELECT id, filename, original_name, mime_type, file_size, width, height, alt_text, created_at
             FROM media WHERE id = ? LIMIT 1',
            [$id]
        );

        if (!$row) return false;
        $row['url'] = self::urlFromFilename($row['filename']);
        return $row;
    }

    public static function delete(int $id): bool
    {
        $record = self::getById($id);
        if (!$record) return false;

        // Derive disk path from filename — scan uploads/ for it
        $path = self::diskPathFromFilename($record['filename']);
        if ($path && file_exists($path)) {
            @unlink($path);
        }

        $db      = Database::getInstance();
        $deleted = $db->delete('media', 'id = ?', [$id]);
        return $deleted > 0;
    }

    public static function toWebP(string $sourcePath, string $destPath): bool
    {
        if (!function_exists('imagewebp')) return false;

        // Large images can exhaust the default memory limit; raise it for conversion.
        $current = ini_get('memory_limit');
        $currentBytes = self::parseMemoryLimit($current);
        if ($currentBytes < 256 * 1024 * 1024) {
            ini_set('memory_limit', '256M');
        }

        $finfo    = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($sourcePath);

        $image = match ($mimeType) {
            'image/jpeg' => @imagecreatefromjpeg($sourcePath),
            'image/gif'  => @imagecreatefromgif($sourcePath),
            'image/png'  => (function () use ($sourcePath) {
                $img = @imagecreatefrompng($sourcePath);
                if ($img) { imagepalettetotruecolor($img); imagealphablending($img, true); imagesavealpha($img, true); }
                return $img;
            })(),
            default => false,
        };

        if (!$image) return false;

        $quality = defined('WEBP_QUALITY') ? (int)WEBP_QUALITY : 85;
        $result  = imagewebp($image, $destPath, $quality);
        imagedestroy($image);
        return $result;
    }

    public static function getDimensions(string $path): array
    {
        if (!file_exists($path)) return [0, 0];
        $size = @getimagesize($path);
        return $size ? [(int)$size[0], (int)$size[1]] : [0, 0];
    }

    // ─── Private helpers ───────────────────────────────────────────────────

    private static function urlFromFilename(string $filename): string
    {
        // Filename format: name_<hex8>_tmp? . ext  — find it under uploads/
        $path = self::diskPathFromFilename($filename);
        if (!$path) return SITE_URL . '/uploads/' . $filename;

        $relative = ltrim(str_replace(rtrim(ROOT_PATH, '/'), '', $path), '/');
        return rtrim(SITE_URL, '/') . '/' . $relative;
    }

    private static function parseMemoryLimit(string $val): int
    {
        $val = trim($val);
        if ($val === '-1') return PHP_INT_MAX;
        $unit = strtolower(substr($val, -1));
        $num  = (int) $val;
        return match ($unit) {
            'g' => $num * 1024 * 1024 * 1024,
            'm' => $num * 1024 * 1024,
            'k' => $num * 1024,
            default => $num,
        };
    }

    private static function diskPathFromFilename(string $filename): string|false
    {
        // Search the two most likely year/month directories first, then fall back to glob
        $base = rtrim(UPLOAD_PATH, '/');
        $candidate = $base . '/' . date('Y') . '/' . date('m') . '/' . $filename;
        if (file_exists($candidate)) return $candidate;

        $found = glob($base . '/*/*/' . $filename);
        return $found ? $found[0] : false;
    }
}
