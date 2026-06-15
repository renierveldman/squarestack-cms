<?php

class Router
{
    public function dispatch(): void
    {
        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';

        // Strip SITE_URL base path
        $basePath = parse_url(SITE_URL, PHP_URL_PATH) ?? '';
        if ($basePath !== '' && strpos($requestUri, $basePath) === 0) {
            $requestUri = substr($requestUri, strlen($basePath));
        }

        // Strip query string
        $pos = strpos($requestUri, '?');
        if ($pos !== false) {
            $requestUri = substr($requestUri, 0, $pos);
        }

        // Decode
        $uri = urldecode($requestUri);
        if ($uri === '') {
            $uri = '/';
        }

        // Check _route param for special routes
        $route = $_GET['_route'] ?? '';

        if ($route === 'sitemap') {
            $this->serveSitemap();
            return;
        }

        if ($route === 'robots') {
            $this->serveRobots();
            return;
        }

        // Admin routing
        if (strpos($uri, '/admin') === 0) {
            $this->dispatchAdmin($uri);
            return;
        }

        // Cache check
        if (Cache::shouldCache()) {
            $cached = Cache::get($uri);
            if ($cached !== false) {
                echo $cached;
                exit;
            }
        }

        $blogSlug = Settings::get('blog_slug', 'blog');

        // Route matching
        if ($uri === '/') {
            $homepageId = (int) Settings::get('homepage_id', 0);
            if ($homepageId > 0) {
                $pageData = CMS::getPage($homepageId);
                if ($pageData) {
                    $GLOBALS['page'] = $pageData;
                    $template = !empty($pageData['template']) ? $pageData['template'] : 'page';
                    $this->loadTemplate($template);
                    return;
                }
            }
            $this->loadTemplate('index');
            return;
        }

        if ($uri === '/' . ltrim($blogSlug, '/')) {
            $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
            $GLOBALS['current_page'] = $page;
            $this->loadTemplate('blog');
            return;
        }

        $blogPrefix = '/' . ltrim($blogSlug, '/') . '/';
        if (strpos($uri, $blogPrefix) === 0) {
            $slug = substr($uri, strlen($blogPrefix));
            $slug = trim($slug, '/');
            $post = CMS::getPost($slug);
            if ($post) {
                $GLOBALS['post'] = $post;
                $this->loadTemplate('single');
                return;
            }
        }

        // Single-segment slug — try page
        $slug = trim($uri, '/');
        if ($slug !== '' && strpos($slug, '/') === false) {
            $pageData = CMS::getPage($slug);
            if ($pageData) {
                $GLOBALS['page'] = $pageData;
                $template = !empty($pageData['template']) ? $pageData['template'] : 'page';
                $this->loadTemplate($template);
                return;
            }
        }

        // 404 fallback
        http_response_code(404);
        $this->loadTemplate('404');
    }

    private function loadTemplate(string $name): void
    {
        global $page, $post, $posts, $category, $cms;

        $page     = $GLOBALS['page']     ?? null;
        $post     = $GLOBALS['post']     ?? null;
        $posts    = $GLOBALS['posts']    ?? null;
        $category = $GLOBALS['category'] ?? null;
        $cms      = 'CMS';

        $templateFile = THEME_PATH . '/templates/' . $name . '.php';

        ob_start();

        if (file_exists($templateFile)) {
            require $templateFile;
        } else {
            // Fallback: emit a bare 404 if template file itself is missing
            if ($name !== '404') {
                http_response_code(404);
                $fallback = THEME_PATH . '/templates/404.php';
                if (file_exists($fallback)) {
                    require $fallback;
                } else {
                    echo '<!DOCTYPE html><html><body><h1>404 Not Found</h1></body></html>';
                }
            } else {
                echo '<!DOCTYPE html><html><body><h1>404 Not Found</h1></body></html>';
            }
        }

        $output = ob_get_clean();

        $uri = $this->currentUri();
        if (Cache::shouldCache()) {
            Cache::set($uri, $output);
        }

        echo $output;
    }

    private function currentUri(): string
    {
        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';

        $basePath = parse_url(SITE_URL, PHP_URL_PATH) ?? '';
        if ($basePath !== '' && strpos($requestUri, $basePath) === 0) {
            $requestUri = substr($requestUri, strlen($basePath));
        }

        $pos = strpos($requestUri, '?');
        if ($pos !== false) {
            $requestUri = substr($requestUri, 0, $pos);
        }

        $uri = urldecode($requestUri);
        return $uri === '' ? '/' : $uri;
    }

    private function dispatchAdmin(string $uri): void
    {
        $adminUri = substr($uri, strlen('/admin'));
        if ($adminUri === '' || $adminUri === '/') {
            $adminUri = '/index';
        }

        $adminUri = '/' . ltrim($adminUri, '/');
        $file = ADMIN_PATH . $adminUri . '.php';

        if (file_exists($file)) {
            include $file;
        } else {
            $index = ADMIN_PATH . '/index.php';
            if (file_exists($index)) {
                include $index;
            } else {
                http_response_code(404);
                echo '<!DOCTYPE html><html><body><h1>404 Not Found</h1></body></html>';
            }
        }
    }

    private function serveSitemap(): void
    {
        header('Content-Type: application/xml; charset=utf-8');

        $siteUrl = rtrim(SITE_URL, '/');
        $blogSlug = Settings::get('blog_slug', 'blog');

        $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        // Homepage
        $xml .= '  <url><loc>' . htmlspecialchars($siteUrl . '/') . '</loc></url>' . "\n";

        // Blog index
        $xml .= '  <url><loc>' . htmlspecialchars($siteUrl . '/' . $blogSlug) . '</loc></url>' . "\n";

        // Posts
        if (class_exists('CMS')) {
            $posts = CMS::getPosts(['status' => 'published', 'limit' => 10000]);
            if (is_array($posts)) {
                foreach ($posts as $p) {
                    $loc = $siteUrl . '/' . $blogSlug . '/' . ($p['slug'] ?? '');
                    $xml .= '  <url><loc>' . htmlspecialchars($loc) . '</loc>';
                    if (!empty($p['updated_at'])) {
                        $xml .= '<lastmod>' . date('Y-m-d', strtotime($p['updated_at'])) . '</lastmod>';
                    }
                    $xml .= '</url>' . "\n";
                }
            }

            // Pages
            $pages = CMS::getPages(['status' => 'published', 'limit' => 10000]);
            if (is_array($pages)) {
                foreach ($pages as $pg) {
                    $loc = $siteUrl . '/' . ($pg['slug'] ?? '');
                    $xml .= '  <url><loc>' . htmlspecialchars($loc) . '</loc>';
                    if (!empty($pg['updated_at'])) {
                        $xml .= '<lastmod>' . date('Y-m-d', strtotime($pg['updated_at'])) . '</lastmod>';
                    }
                    $xml .= '</url>' . "\n";
                }
            }
        }

        $xml .= '</urlset>';

        echo $xml;
    }

    private function serveRobots(): void
    {
        header('Content-Type: text/plain; charset=utf-8');

        $siteUrl = rtrim(SITE_URL, '/');

        echo "User-agent: *\n";
        echo "Disallow: /admin/\n";
        echo "Disallow: /cache/\n";
        echo "Disallow: /core/\n";
        echo "\n";
        echo "Sitemap: " . $siteUrl . "/?_route=sitemap\n";
    }
}
