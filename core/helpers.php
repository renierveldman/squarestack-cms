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

function get_theme_option(string $key, mixed $default = ''): mixed {
    return CMS::getMeta('option', 0, $key, $default);
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
    $siteName  = Settings::get('site_name', '');
    $suffix    = Settings::get('seo_title_suffix', '');
    $defDesc   = Settings::get('seo_meta_desc', '');
    $defOg     = Settings::get('seo_og_image', '');

    $rawTitle  = $obj['meta_title'] ?? $obj['title'] ?? $siteName ?? '';
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
    echo "<meta property=\"og:site_name\" content=\"" . htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8') . "\">\n";
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

// ─── Forms ──────────────────────────────────────────────────────────────────

function render_form(string $slug): void {
    $formData = CMS::getForm($slug);
    if (!$formData) return;

    $fields  = json_decode($formData['fields'] ?? '[]', true) ?: [];
    $formId  = (int) $formData['id'];
    $handler = SITE_URL . '/form-handler.php';

    echo '<form class="ss-form space-y-5" data-form-id="' . $formId . '" novalidate>';
    echo '<input type="hidden" name="form_id" value="' . $formId . '">';
    echo '<input type="text" name="website" style="display:none" tabindex="-1" autocomplete="off">';

    foreach ($fields as $field) {
        $type        = $field['type']        ?? 'text';
        $label       = $field['label']       ?? '';
        $name        = $field['name']        ?? '';
        $placeholder = $field['placeholder'] ?? '';
        $required    = !empty($field['required']);
        $options     = $field['options']     ?? [];
        $reqAttr     = $required ? ' required' : '';
        $inputClass  = 'w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition';

        echo '<div class="ss-form-group" data-name="' . esc_attr($name) . '">';

        if ($type !== 'checkbox' && $label) {
            echo '<label class="block text-sm font-medium text-gray-700 mb-1.5">';
            echo esc_html($label);
            if ($required) echo ' <span class="text-red-500" aria-hidden="true">*</span>';
            echo '</label>';
        }

        switch ($type) {
            case 'textarea':
                echo '<textarea name="' . esc_attr($name) . '" rows="4" placeholder="' . esc_attr($placeholder) . '" class="' . $inputClass . ' resize-none"' . $reqAttr . '></textarea>';
                break;
            case 'select':
                echo '<select name="' . esc_attr($name) . '" class="' . $inputClass . '"' . $reqAttr . '>';
                echo '<option value="">— Select —</option>';
                foreach ($options as $opt) {
                    echo '<option value="' . esc_attr($opt) . '">' . esc_html($opt) . '</option>';
                }
                echo '</select>';
                break;
            case 'checkbox':
                echo '<label class="flex items-start gap-3 cursor-pointer">';
                echo '<input type="checkbox" name="' . esc_attr($name) . '" value="1" class="mt-0.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"' . $reqAttr . '>';
                echo '<span class="text-sm text-gray-700">' . esc_html($label) . ($required ? ' <span class="text-red-500">*</span>' : '') . '</span>';
                echo '</label>';
                break;
            default:
                echo '<input type="' . esc_attr($type) . '" name="' . esc_attr($name) . '" placeholder="' . esc_attr($placeholder) . '" class="' . $inputClass . '"' . $reqAttr . '>';
        }

        echo '<p class="ss-field-error text-xs text-red-600 mt-1 hidden"></p>';
        echo '</div>';
    }

    echo '<div class="ss-form-success hidden bg-green-50 border border-green-200 text-green-700 rounded-lg px-4 py-3 text-sm"></div>';
    echo '<div class="ss-form-error hidden bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 text-sm"></div>';

    echo '<div>';
    echo '<button type="submit" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-6 py-2.5 rounded-lg transition">';
    echo '<span class="ss-btn-label">Send Message</span>';
    echo '<i class="ss-btn-spinner fa-solid fa-spinner fa-spin hidden"></i>';
    echo '</button>';
    echo '</div>';

    echo '</form>';

    // Inline JS — output once per page
    static $scriptPrinted = false;
    if (!$scriptPrinted) {
        $scriptPrinted = true;
        $handlerUrl = SITE_URL . '/form-handler.php';
        echo <<<JS
<script>
(function () {
    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!form.classList.contains('ss-form')) return;
        e.preventDefault();

        var btn     = form.querySelector('[type="submit"]');
        var label   = form.querySelector('.ss-btn-label');
        var spinner = form.querySelector('.ss-btn-spinner');
        var success = form.querySelector('.ss-form-success');
        var errBox  = form.querySelector('.ss-form-error');

        // Clear previous errors
        form.querySelectorAll('.ss-field-error').forEach(function (el) { el.classList.add('hidden'); el.textContent = ''; });
        success.classList.add('hidden');
        errBox.classList.add('hidden');

        btn.disabled = true;
        if (label) label.textContent = 'Sending…';
        if (spinner) spinner.classList.remove('hidden');

        var body = new FormData(form);

        fetch('{$handlerUrl}', { method: 'POST', body: body })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                btn.disabled = false;
                if (label) label.textContent = 'Send Message';
                if (spinner) spinner.classList.add('hidden');

                if (data.success) {
                    if (data.redirect) {
                        window.location.href = data.redirect;
                        return;
                    }
                    form.reset();
                    success.textContent = data.message || 'Thank you!';
                    success.classList.remove('hidden');
                } else if (data.errors) {
                    Object.keys(data.errors).forEach(function (name) {
                        var group = form.querySelector('[data-name="' + name + '"]');
                        if (group) {
                            var errEl = group.querySelector('.ss-field-error');
                            if (errEl) { errEl.textContent = data.errors[name]; errEl.classList.remove('hidden'); }
                        }
                    });
                } else {
                    errBox.textContent = data.error || 'Something went wrong. Please try again.';
                    errBox.classList.remove('hidden');
                }
            })
            .catch(function () {
                btn.disabled = false;
                if (label) label.textContent = 'Send Message';
                if (spinner) spinner.classList.add('hidden');
                errBox.textContent = 'Network error. Please try again.';
                errBox.classList.remove('hidden');
            });
    });
}());
</script>
JS;
    }
}

// ─── Integrations (marketing/analytics snippets) ────────────────────────────

function integrations_head(): void {
    $gtmId   = Settings::get('gtm_id',           '');
    $gaId    = Settings::get('ga_id',             '');
    $pixelId = Settings::get('meta_pixel_id',     '');
    $gsc     = Settings::get('gsc_verification',  '');

    if ($gsc) {
        echo "<meta name=\"google-site-verification\" content=\"" . htmlspecialchars($gsc, ENT_QUOTES, 'UTF-8') . "\">\n";
    }

    if ($gtmId) {
        $id = htmlspecialchars($gtmId, ENT_QUOTES, 'UTF-8');
        echo "<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','{$id}');</script>\n";
    } elseif ($gaId) {
        // Only inject GA4 directly when GTM is not managing it
        $id = htmlspecialchars($gaId, ENT_QUOTES, 'UTF-8');
        echo "<script async src=\"https://www.googletagmanager.com/gtag/js?id={$id}\"></script>\n";
        echo "<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','{$id}');</script>\n";
    }

    if ($pixelId) {
        $id = htmlspecialchars($pixelId, ENT_QUOTES, 'UTF-8');
        echo "<script>!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');fbq('init','{$id}');fbq('track','PageView');</script>\n";
        echo "<noscript><img height=\"1\" width=\"1\" style=\"display:none\" src=\"https://www.facebook.com/tr?id={$id}&ev=PageView&noscript=1\"></noscript>\n";
    }
}

function integrations_body_open(): void {
    $gtmId = Settings::get('gtm_id', '');
    if (!$gtmId) return;
    $id = htmlspecialchars($gtmId, ENT_QUOTES, 'UTF-8');
    echo "<noscript><iframe src=\"https://www.googletagmanager.com/ns.html?id={$id}\" height=\"0\" width=\"0\" style=\"display:none;visibility:hidden\"></iframe></noscript>\n";
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
