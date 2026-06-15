<?php

class CMS
{
    private static array $fieldGroups = [];

    // -------------------------------------------------------------------------
    // PAGES
    // -------------------------------------------------------------------------

    public static function getPage(string|int $slugOrId): array|false
    {
        $db = Database::getInstance();
        if (is_int($slugOrId) || ctype_digit((string) $slugOrId)) {
            return $db->fetch('SELECT * FROM `pages` WHERE `id` = ? LIMIT 1', [(int) $slugOrId]);
        }
        return $db->fetch('SELECT * FROM `pages` WHERE `slug` = ? LIMIT 1', [$slugOrId]);
    }

    public static function getPages(array $opts = []): array
    {
        $db = Database::getInstance();
        $params = [];
        $where = [];

        if (isset($opts['status'])) {
            $where[] = '`status` = ?';
            $params[] = $opts['status'];
        }

        $sql = 'SELECT * FROM `pages`';
        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $orderBy = $opts['order_by'] ?? 'created_at DESC';
        $sql .= ' ORDER BY ' . $orderBy;

        if (isset($opts['limit'])) {
            $sql .= ' LIMIT ?';
            $params[] = (int) $opts['limit'];
            if (isset($opts['offset'])) {
                $sql .= ' OFFSET ?';
                $params[] = (int) $opts['offset'];
            }
        }

        return $db->fetchAll($sql, $params);
    }

    public static function savePage(array $data): int
    {
        $db = Database::getInstance();

        if (isset($data['id']) && $data['id']) {
            $id = (int) $data['id'];
            unset($data['id']);
            $data['updated_at'] = date('Y-m-d H:i:s');
            $db->update('pages', $data, '`id` = ?', [$id]);
            Cache::flush();
            return $id;
        }

        if (!isset($data['created_at'])) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }
        if (!isset($data['updated_at'])) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }
        $id = $db->insert('pages', $data);
        Cache::flush();
        return $id;
    }

    public static function deletePage(int $id): bool
    {
        $db = Database::getInstance();
        $rows = $db->delete('pages', '`id` = ?', [$id]);
        if ($rows > 0) {
            Cache::flush();
            return true;
        }
        return false;
    }

    // -------------------------------------------------------------------------
    // POSTS
    // -------------------------------------------------------------------------

    public static function getPost(string|int $slugOrId): array|false
    {
        $db = Database::getInstance();
        if (is_int($slugOrId) || ctype_digit((string) $slugOrId)) {
            return $db->fetch('SELECT * FROM `posts` WHERE `id` = ? LIMIT 1', [(int) $slugOrId]);
        }
        return $db->fetch('SELECT * FROM `posts` WHERE `slug` = ? LIMIT 1', [$slugOrId]);
    }

    public static function getPosts(array $opts = []): array
    {
        $db = Database::getInstance();
        $params = [];
        $countParams = [];
        $where = [];

        if (isset($opts['status'])) {
            $where[] = '`status` = ?';
            $params[] = $opts['status'];
            $countParams[] = $opts['status'];
        }

        if (isset($opts['category_id'])) {
            $where[] = '`category_id` = ?';
            $params[] = (int) $opts['category_id'];
            $countParams[] = (int) $opts['category_id'];
        }

        $whereClause = $where ? ' WHERE ' . implode(' AND ', $where) : '';

        $countSql = 'SELECT COUNT(*) as `total` FROM `posts`' . $whereClause;
        $countRow = $db->fetch($countSql, $countParams);
        $total = (int) ($countRow['total'] ?? 0);

        $orderBy = $opts['order_by'] ?? 'created_at DESC';
        $limit = isset($opts['limit']) ? (int) $opts['limit'] : 10;

        $sql = 'SELECT * FROM `posts`' . $whereClause . ' ORDER BY ' . $orderBy . ' LIMIT ?';
        $params[] = $limit;

        if (isset($opts['offset'])) {
            $sql .= ' OFFSET ?';
            $params[] = (int) $opts['offset'];
        }

        $items = $db->fetchAll($sql, $params);
        $pages = (int) ceil($total / $limit);

        return [
            'items' => $items,
            'total' => $total,
            'pages' => $pages,
        ];
    }

    public static function savePost(array $data): int
    {
        $db = Database::getInstance();

        if (isset($data['id']) && $data['id']) {
            $id = (int) $data['id'];
            unset($data['id']);
            $data['updated_at'] = date('Y-m-d H:i:s');
            $db->update('posts', $data, '`id` = ?', [$id]);
            return $id;
        }

        if (!isset($data['created_at'])) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }
        if (!isset($data['updated_at'])) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }
        return $db->insert('posts', $data);
    }

    public static function deletePost(int $id): bool
    {
        $db = Database::getInstance();
        return $db->delete('posts', '`id` = ?', [$id]) > 0;
    }

    // -------------------------------------------------------------------------
    // CATEGORIES
    // -------------------------------------------------------------------------

    public static function getCategories(): array
    {
        $db = Database::getInstance();
        return $db->fetchAll('SELECT * FROM `categories` ORDER BY `name` ASC');
    }

    public static function getCategory(string|int $slugOrId): array|false
    {
        $db = Database::getInstance();
        if (is_int($slugOrId) || ctype_digit((string) $slugOrId)) {
            return $db->fetch('SELECT * FROM `categories` WHERE `id` = ? LIMIT 1', [(int) $slugOrId]);
        }
        return $db->fetch('SELECT * FROM `categories` WHERE `slug` = ? LIMIT 1', [$slugOrId]);
    }

    // -------------------------------------------------------------------------
    // MENUS
    // -------------------------------------------------------------------------

    public static function getMenu(string $location): array
    {
        $db = Database::getInstance();
        $menu = $db->fetch('SELECT * FROM `menus` WHERE `location` = ? LIMIT 1', [$location]);
        if (!$menu) {
            return [];
        }

        $items = $db->fetchAll(
            'SELECT * FROM `menu_items` WHERE `menu_id` = ? ORDER BY `parent_id` ASC, `sort_order` ASC',
            [$menu['id']]
        );

        $menu['items'] = self::nestMenuItems($items);
        return $menu;
    }

    private static function nestMenuItems(array $items, int $parentId = 0): array
    {
        $nested = [];
        foreach ($items as $item) {
            $itemParent = isset($item['parent_id']) ? (int) $item['parent_id'] : 0;
            if ($itemParent === $parentId) {
                $item['children'] = self::nestMenuItems($items, (int) $item['id']);
                $nested[] = $item;
            }
        }
        return $nested;
    }

    public static function saveMenu(string $location, string $name, array $items): void
    {
        $db = Database::getInstance();
        $existing = $db->fetch('SELECT `id` FROM `menus` WHERE `location` = ? LIMIT 1', [$location]);

        if ($existing) {
            $menuId = (int) $existing['id'];
            $db->update('menus', ['name' => $name], '`id` = ?', [$menuId]);
        } else {
            $menuId = $db->insert('menus', ['location' => $location, 'name' => $name]);
        }

        $db->delete('menu_items', '`menu_id` = ?', [$menuId]);

        self::insertMenuItems($db, $menuId, $items, 0);
    }

    private static function insertMenuItems(Database $db, int $menuId, array $items, int $parentId, int &$sort = 0): void
    {
        foreach ($items as $item) {
            $sort++;
            $children = $item['children'] ?? [];
            unset($item['children'], $item['id']);
            $item['menu_id'] = $menuId;
            $item['parent_id'] = $parentId;
            $item['sort_order'] = $sort;
            $newId = $db->insert('menu_items', $item);
            if ($children) {
                self::insertMenuItems($db, $menuId, $children, $newId, $sort);
            }
        }
    }

    // -------------------------------------------------------------------------
    // META / CUSTOM FIELDS
    // -------------------------------------------------------------------------

    public static function getMeta(string $type, int $id, string $key, mixed $default = null): mixed
    {
        $db = Database::getInstance();
        $row = $db->fetch(
            'SELECT `meta_value` FROM `meta` WHERE `type` = ? AND `object_id` = ? AND `meta_key` = ? LIMIT 1',
            [$type, $id, $key]
        );
        if (!$row) {
            return $default;
        }
        $value = $row['meta_value'];
        $decoded = json_decode($value, true);
        return (json_last_error() === JSON_ERROR_NONE) ? $decoded : $value;
    }

    public static function saveMeta(string $type, int $id, string $key, mixed $value): void
    {
        $db = Database::getInstance();
        $encoded = is_string($value) ? $value : json_encode($value);
        $existing = $db->fetch(
            'SELECT `id` FROM `meta` WHERE `type` = ? AND `object_id` = ? AND `meta_key` = ? LIMIT 1',
            [$type, $id, $key]
        );
        if ($existing) {
            $db->update('meta', ['meta_value' => $encoded], '`type` = ? AND `object_id` = ? AND `meta_key` = ?', [$type, $id, $key]);
        } else {
            $db->insert('meta', [
                'type'       => $type,
                'object_id'  => $id,
                'meta_key'   => $key,
                'meta_value' => $encoded,
            ]);
        }
    }

    public static function getAllMeta(string $type, int $id): array
    {
        $db = Database::getInstance();
        $rows = $db->fetchAll(
            'SELECT `meta_key`, `meta_value` FROM `meta` WHERE `type` = ? AND `object_id` = ?',
            [$type, $id]
        );
        $result = [];
        foreach ($rows as $row) {
            $decoded = json_decode($row['meta_value'], true);
            $result[$row['meta_key']] = (json_last_error() === JSON_ERROR_NONE) ? $decoded : $row['meta_value'];
        }
        return $result;
    }

    // -------------------------------------------------------------------------
    // FIELD GROUPS
    // -------------------------------------------------------------------------

    public static function registerFieldGroup(array $config): void
    {
        self::$fieldGroups[] = $config;
    }

    public static function getFieldGroups(string $location, string $template = ''): array
    {
        $matched = [];
        foreach (self::$fieldGroups as $group) {
            $groupLocation = $group['location'] ?? null;
            $groupTemplate = $group['template'] ?? '';

            if ($groupLocation !== $location) {
                continue;
            }

            if ($groupTemplate !== '' && $template !== '' && $groupTemplate !== $template) {
                continue;
            }

            $matched[] = $group;
        }
        return $matched;
    }

    // -------------------------------------------------------------------------
    // SITEMAP
    // -------------------------------------------------------------------------

    public static function generateSitemap(): string
    {
        $baseUrl = defined('SITE_URL') ? rtrim(SITE_URL, '/') : '';

        $pages = self::getPages(['status' => 'published']);
        $postResult = self::getPosts(['status' => 'published', 'limit' => 1000]);
        $posts = $postResult['items'];

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($pages as $page) {
            $slug = $page['slug'] ?? '';
            $loc = $slug === '' || $slug === 'home' ? $baseUrl . '/' : $baseUrl . '/' . htmlspecialchars($slug, ENT_XML1) . '/';
            $lastmod = isset($page['updated_at']) ? date('Y-m-d', strtotime($page['updated_at'])) : date('Y-m-d');
            $xml .= "  <url>\n";
            $xml .= "    <loc>{$loc}</loc>\n";
            $xml .= "    <lastmod>{$lastmod}</lastmod>\n";
            $xml .= "    <changefreq>weekly</changefreq>\n";
            $xml .= "  </url>\n";
        }

        foreach ($posts as $post) {
            $slug = $post['slug'] ?? '';
            if ($slug === '') {
                continue;
            }
            $loc = $baseUrl . '/blog/' . htmlspecialchars($slug, ENT_XML1) . '/';
            $lastmod = isset($post['updated_at']) ? date('Y-m-d', strtotime($post['updated_at'])) : date('Y-m-d');
            $xml .= "  <url>\n";
            $xml .= "    <loc>{$loc}</loc>\n";
            $xml .= "    <lastmod>{$lastmod}</lastmod>\n";
            $xml .= "    <changefreq>monthly</changefreq>\n";
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';
        return $xml;
    }
}
