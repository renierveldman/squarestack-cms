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
            throw new RuntimeException('Upload error: ' . ($file['error'] ?? 'unknown'));
        }

        $maxSize = defined('MAX_UPLOAD_SIZE') ? MAX_UPLOAD_SIZE : 10 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            throw new RuntimeException('File exceeds maximum allowed size.');
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        if (!in_array($mimeType, self::$allowedMimes, true)) {
            throw new RuntimeException('File type not allowed: ' . $mimeType);
        }

        $year  = date('Y');
        $month = date('m');
        $uploadDir = rtrim(UPLOAD_PATH, '/') . '/' . $year . '/' . $month . '/';

        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
                throw new RuntimeException('Failed to create upload directory.');
            }
        }

        $originalName = pathinfo($file['name'], PATHINFO_FILENAME);
        $originalName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $originalName);
        $uniqueId     = bin2hex(random_bytes(8));

        $shouldConvert = in_array($mimeType, self::$convertibleMimes, true);

        if ($shouldConvert) {
            $filename = $originalName . '_' . $uniqueId . '.webp';
        } else {
            $extensions = [
                'image/webp'      => 'webp',
                'application/pdf' => 'pdf',
            ];
            $ext      = $extensions[$mimeType] ?? pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = $originalName . '_' . $uniqueId . '.' . $ext;
        }

        $destPath = $uploadDir . $filename;

        if ($shouldConvert) {
            $tempPath = $uploadDir . $originalName . '_' . $uniqueId . '_tmp.' . pathinfo($file['name'], PATHINFO_EXTENSION);
            if (!move_uploaded_file($file['tmp_name'], $tempPath)) {
                throw new RuntimeException('Failed to move uploaded file.');
            }
            $converted = self::toWebP($tempPath, $destPath);
            @unlink($tempPath);
            if (!$converted) {
                throw new RuntimeException('Failed to convert image to WebP.');
            }
        } else {
            if (!move_uploaded_file($file['tmp_name'], $destPath)) {
                throw new RuntimeException('Failed to move uploaded file.');
            }
        }

        [$width, $height] = self::getDimensions($destPath);

        $url = rtrim(SITE_URL, '/') . '/uploads/' . $year . '/' . $month . '/' . $filename;

        $db   = Database::getInstance();
        $stmt = $db->prepare(
            'INSERT INTO media (filename, url, mime_type, size, width, height, created_at)
             VALUES (:filename, :url, :mime_type, :size, :width, :height, NOW())'
        );
        $stmt->execute([
            ':filename'  => $filename,
            ':url'       => $url,
            ':mime_type' => $shouldConvert ? 'image/webp' : $mimeType,
            ':size'      => filesize($destPath),
            ':width'     => $width,
            ':height'    => $height,
        ]);
        $id = (int) $db->lastInsertId();

        return [
            'id'       => $id,
            'url'      => $url,
            'filename' => $filename,
            'width'    => $width,
            'height'   => $height,
        ];
    }

    public static function getLibrary(int $page = 1, int $perPage = 24): array
    {
        $page    = max(1, $page);
        $perPage = max(1, $perPage);
        $offset  = ($page - 1) * $perPage;

        $db = Database::getInstance();

        $totalStmt = $db->query('SELECT COUNT(*) FROM media');
        $total     = (int) $totalStmt->fetchColumn();
        $pages     = (int) ceil($total / $perPage);

        $stmt = $db->prepare(
            'SELECT id, filename, url, mime_type, size, width, height, created_at
             FROM media
             ORDER BY created_at DESC
             LIMIT :limit OFFSET :offset'
        );
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'items' => $items,
            'total' => $total,
            'pages' => $pages,
        ];
    }

    public static function getById(int $id): array|false
    {
        $db   = Database::getInstance();
        $stmt = $db->prepare(
            'SELECT id, filename, url, mime_type, size, width, height, created_at
             FROM media
             WHERE id = :id
             LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row !== false ? $row : false;
    }

    public static function delete(int $id): bool
    {
        $record = self::getById($id);
        if ($record === false) {
            return false;
        }

        $url      = $record['url'];
        $siteUrl  = rtrim(SITE_URL, '/');
        $relative = ltrim(str_replace($siteUrl, '', $url), '/');

        $docRoot  = defined('DOC_ROOT') ? rtrim(DOC_ROOT, '/') : rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/');
        $filePath = $docRoot . '/' . $relative;

        if (file_exists($filePath)) {
            @unlink($filePath);
        }

        $db   = Database::getInstance();
        $stmt = $db->prepare('DELETE FROM media WHERE id = :id');
        $stmt->execute([':id' => $id]);

        return $stmt->rowCount() > 0;
    }

    public static function toWebP(string $sourcePath, string $destPath): bool
    {
        if (!function_exists('imagewebp')) {
            return false;
        }

        $finfo    = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($sourcePath);

        $image = null;
        switch ($mimeType) {
            case 'image/jpeg':
                $image = @imagecreatefromjpeg($sourcePath);
                break;
            case 'image/png':
                $image = @imagecreatefrompng($sourcePath);
                if ($image !== false) {
                    imagepalettetotruecolor($image);
                    imagealphablending($image, true);
                    imagesavealpha($image, true);
                }
                break;
            case 'image/gif':
                $image = @imagecreatefromgif($sourcePath);
                break;
            default:
                return false;
        }

        if ($image === false || $image === null) {
            return false;
        }

        $quality = defined('WEBP_QUALITY') ? (int) WEBP_QUALITY : 82;
        $result  = imagewebp($image, $destPath, $quality);
        imagedestroy($image);

        return $result;
    }

    public static function getDimensions(string $path): array
    {
        if (!file_exists($path)) {
            return [0, 0];
        }

        $size = @getimagesize($path);
        if ($size === false) {
            return [0, 0];
        }

        return [(int) $size[0], (int) $size[1]];
    }
}
