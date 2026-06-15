<?php
// Template helper functions — used inside theme templates.
// Router sets globals: $page, $post, $posts, $category, $currentPage, $totalPages

function the_title(): void {
    global $page, $post;
    echo htmlspecialchars(get_the_title(), ENT_QUOTES, 'UTF-8');
}

function get_the_title(): string {
    global $page, $post;
    if (!empty($page['title'])) return $page['title'];
    if (!empty($post['title'])) return $post['title'];
    return '';
}

function the_content(): void {
    global $page, $post;
    echo get_the_content();
}

function get_the_content(): string {
    global $page, $post;
    if (!empty($page['content'])) return $page['content'];
    if (!empty($post['content'])) return $post['content'];
    return '';
}

function the_permalink(): void {
    echo htmlspecialchars(get_the_permalink(), ENT_QUOTES, 'UTF-8');
}

function get_the_permalink(): string {
    global $page, $post;
    if (!empty($post['slug'])) {
        $blogSlug = Settings::get('blog_slug', 'blog');
        return SITE_URL . '/' . $blogSlug . '/' . $post['slug'];
    }
    if (!empty($page['slug'])) {
        return SITE_URL . '/' . $page['slug'];
    }
    return SITE_URL . '/';
}

// ─── Custom Fields ─────────────────────────────────────────────────────────

function get_field(string $key, ?int $id = null): mixed {
    global $page, $post;
    if ($id === null) {
        if (!empty($page['id'])) {
            return CMS::getMeta('page', (int)$page['id'], $key);
        }
        if (!empty($post['id'])) {
            return CMS::getMeta('post', (int)$post['id'], $key);
        }
        return null;
    }
    global $cms_object_type;
    $type = $cms_object_type ?? 'page';
    return CMS::getMeta($type, $id, $key);
}

function the_field(string $key): void {
    $val = get_field($key);
    echo htmlspecialchars((string)$val, ENT_QUOTES, 'UTF-8');
}

function register_field_group(array $config): void {
    CMS::registerFieldGroup($config);
}

// ─── Posts / Pages ─────────────────────────────────────────────────────────

function get_posts(array $args = []): array {
    $result = CMS::getPosts($args);
    return $result['items'] ?? $result;
}

function get_page(string|int $slug): array|false {
    return CMS::getPage($slug);
}

// ─── Menus ─────────────────────────────────────────────────────────────────

function get_menu(string $location): array {
    return CMS::getMenu($location);
}

function render_menu(string $location, string $ulClass = '', string $liClass = '', string $aClass = ''): void {
    $menu = CMS::getMenu($location);
    if (empty($menu['items'])) return;

    $currentUri = strtok($_SERVER['REQUEST_URI'], '?');

    echo '<ul' . ($ulClass ? ' class="' . htmlspecialchars($ulClass) . '"' : '') . '>';
    foreach ($menu['items'] as $item) {
        $isActive = rtrim($currentUri, '/') === rtrim(parse_url($item['url'], PHP_URL_PATH), '/');
        $liExtra = $isActive ? ' active' : '';
        echo '<li' . ($liClass ? ' class="' . htmlspecialchars($liClass . $liExtra) . '"' : ($liExtra ? ' class="active"' : '')) . '>';
        $target = !empty($item['target']) && $item['target'] === '_blank' ? ' target="_blank" rel="noopener"' : '';
        echo '<a href="' . htmlspecialchars($item['url']) . '"'
            . ($aClass ? ' class="' . htmlspecialchars($aClass . ($isActive ? ' text-indigo-600' : '')) . '"' : '')
            . $target . '>'
            . htmlspecialchars($item['label'])
            . '</a>';
        echo '</li>';
    }
    echo '</ul>';
}

// ─── Site / URLs ────────────────────────────────────────────────────────────

function get_site(string $key = ''): mixed {
    if ($key === '') return Settings::all();
    return Settings::get($key, '');
}

function site_url(string $path = ''): string {
    return SITE_URL . $path;
}

function theme_url(string $path = ''): string {
    return SITE_URL . '/themes/' . THEME . $path;
}

function upload_url(string $path = ''): string {
    return SITE_URL . '/uploads' . $path;
}

// ─── SEO ────────────────────────────────────────────────────────────────────

function seo_head(): void {
    global $page, $post;

    $obj       = $post ?: $page;
    $siteName  = Settings::get('site_name', 'My Website');
    $suffix    = Settings::get('seo_default_title_suffix', ' | ' . $siteName);
    $defDesc   = Settings::get('seo_default_description', '');
    $defOg     = Settings::get('seo_default_og_image', '');

    $rawTitle  = $obj['meta_title'] ?? $obj['title'] ?? $siteName;
    $title     = htmlspecialchars($rawTitle . $suffix, ENT_QUOTES, 'UTF-8');
    $desc      = htmlspecialchars($obj['meta_description'] ?? $defDesc, ENT_QUOTES, 'UTF-8');
    $ogImage   = htmlspecialchars($obj['og_image'] ?? $defOg, ENT_QUOTES, 'UTF-8');
    $canonical = htmlspecialchars(SITE_URL . strtok($_SERVER['REQUEST_URI'], '?'), ENT_QUOTES, 'UTF-8');

    echo "<title>{$title}</title>\n";
    if ($desc) echo "<meta name=\"description\" content=\"{$desc}\">\n";
    echo "<link rel=\"canonical\" href=\"{$canonical}\">\n";
    echo "<meta property=\"og:title\" content=\"{$title}\">\n";
    if ($desc) echo "<meta property=\"og:description\" content=\"{$desc}\">\n";
    if ($ogImage) echo "<meta property=\"og:image\" content=\"{$ogImage}\">\n";
    echo "<meta property=\"og:url\" content=\"{$canonical}\">\n";
    echo "<meta property=\"og:type\" content=\"website\">\n";
    echo "<meta name=\"twitter:card\" content=\"summary_large_image\">\n";

    // JSON-LD
    $schema = [
        '@context' => 'https://schema.org',
        '@type'    => isset($post) ? 'BlogPosting' : 'WebPage',
        'name'     => $rawTitle,
        'url'      => SITE_URL . strtok($_SERVER['REQUEST_URI'], '?'),
    ];
    if ($desc) $schema['description'] = $obj['meta_description'] ?? $defDesc;
    echo '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "</script>\n";
}

function google_fonts_head(): void {
    $raw = Settings::get('google_fonts', '');
    if (!$raw) return;
    $fonts = json_decode($raw, true);
    if (!is_array($fonts) || empty($fonts)) return;

    $families = [];
    foreach ($fonts as $f) {
        if (empty($f['family'])) continue;
        $weights = !empty($f['weights']) ? ':wght@' . implode(';', array_map('trim', explode(',', $f['weights']))) : '';
        $families[] = 'family=' . urlencode($f['family']) . $weights;
    }
    if (!$families) return;

    $href = 'https://fonts.googleapis.com/css2?' . implode('&', $families) . '&display=swap';
    echo "<link rel=\"preconnect\" href=\"https://fonts.googleapis.com\">\n";
    echo "<link rel=\"preconnect\" href=\"https://fonts.gstatic.com\" crossorigin>\n";
    echo "<link rel=\"preload\" href=\"" . htmlspecialchars($href) . "\" as=\"style\" onload=\"this.onload=null;this.rel='stylesheet'\">\n";
    echo "<noscript><link rel=\"stylesheet\" href=\"" . htmlspecialchars($href) . "\"></noscript>\n";
}

// ─── Escape helpers (compat aliases) ────────────────────────────────────────

function esc_html(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

function esc_url(string $url): string {
    return htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
}

function esc_attr(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// ─── Media ──────────────────────────────────────────────────────────────────

function the_image(string $url, string $alt = '', string $class = '', bool $lazy = true): void {
    if (!$url) return;
    $size = @getimagesize($url);
    $w = $size ? ' width="' . $size[0] . '"' : '';
    $h = $size ? ' height="' . $size[1] . '"' : '';
    $loading = $lazy ? ' loading="lazy"' : '';
    $cls = $class ? ' class="' . htmlspecialchars($class) . '"' : '';
    echo '<img src="' . htmlspecialchars($url) . '" alt="' . htmlspecialchars($alt) . '"' . $cls . $w . $h . $loading . ' decoding="async">';
}

// ─── Pagination ─────────────────────────────────────────────────────────────

function pagination(int $currentPage, int $totalPages, string $baseUrl): void {
    if ($totalPages <= 1) return;

    $baseUrl = rtrim($baseUrl, '/');
    echo '<nav class="flex items-center justify-center gap-2 mt-12" aria-label="Pagination">';

    // Prev
    if ($currentPage > 1) {
        $href = $currentPage === 2 ? $baseUrl : $baseUrl . '?page=' . ($currentPage - 1);
        echo '<a href="' . htmlspecialchars($href) . '" class="px-3 py-2 rounded-lg border border-gray-300 text-sm hover:bg-gray-50">← Prev</a>';
    }

    // Page numbers with ellipsis
    $range  = 2;
    $start  = max(1, $currentPage - $range);
    $end    = min($totalPages, $currentPage + $range);

    if ($start > 1) {
        echo '<a href="' . htmlspecialchars($baseUrl) . '" class="px-3 py-2 rounded-lg border border-gray-300 text-sm hover:bg-gray-50">1</a>';
        if ($start > 2) echo '<span class="px-2 text-gray-400">…</span>';
    }

    for ($i = $start; $i <= $end; $i++) {
        $href    = $i === 1 ? $baseUrl : $baseUrl . '?page=' . $i;
        $active  = $i === $currentPage ? ' bg-indigo-600 text-white border-indigo-600' : ' border-gray-300 hover:bg-gray-50';
        echo '<a href="' . htmlspecialchars($href) . '" class="px-3 py-2 rounded-lg border text-sm' . $active . '">' . $i . '</a>';
    }

    if ($end < $totalPages) {
        if ($end < $totalPages - 1) echo '<span class="px-2 text-gray-400">…</span>';
        echo '<a href="' . htmlspecialchars($baseUrl . '?page=' . $totalPages) . '" class="px-3 py-2 rounded-lg border border-gray-300 text-sm hover:bg-gray-50">' . $totalPages . '</a>';
    }

    // Next
    if ($currentPage < $totalPages) {
        echo '<a href="' . htmlspecialchars($baseUrl . '?page=' . ($currentPage + 1)) . '" class="px-3 py-2 rounded-lg border border-gray-300 text-sm hover:bg-gray-50">Next →</a>';
    }

    echo '</nav>';
}
