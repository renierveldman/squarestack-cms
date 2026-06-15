<?php
require_once dirname(__DIR__) . '/config.php';
require_once CORE_PATH . '/Database.php';
require_once CORE_PATH . '/Auth.php';
require_once CORE_PATH . '/CMS.php';
require_once CORE_PATH . '/Settings.php';

Auth::require();
$user = Auth::currentUser();

$success = [];
$errors  = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf']) || !Auth::verifyCsrf($_POST['csrf'])) {
        $errors[] = 'Invalid security token. Please try again.';
    } else {
        $section = $_POST['section'] ?? '';

        if ($section === 'general') {
            Settings::set('site_name',    trim($_POST['site_name']    ?? ''));
            Settings::set('site_tagline', trim($_POST['site_tagline'] ?? ''));
            Settings::set('footer_text',  trim($_POST['footer_text']  ?? ''));
            $success[] = 'general';
        }

        if ($section === 'reading') {
            Settings::set('homepage_id', (int)($_POST['homepage_id'] ?? 0));
            $success[] = 'reading';
        }

        if ($section === 'blog') {
            $blogSlug = trim($_POST['blog_slug'] ?? '');
            $blogSlug = ltrim($blogSlug, '/');
            Settings::set('blog_slug', $blogSlug);
            $success[] = 'blog';
        }

        if ($section === 'google_fonts') {
            $families = $_POST['font_family'] ?? [];
            $weights  = $_POST['font_weights'] ?? [];
            $fonts    = [];
            foreach ($families as $i => $family) {
                $family = trim($family);
                if ($family === '') {
                    continue;
                }
                $fonts[] = [
                    'family'  => $family,
                    'weights' => trim($weights[$i] ?? '400'),
                ];
            }
            Settings::set('google_fonts', json_encode($fonts));
            $success[] = 'google_fonts';
        }

        if ($section === 'analytics') {
            Settings::set('ga_id', trim($_POST['ga_id'] ?? ''));
            $success[] = 'analytics';
        }

        if ($section === 'seo') {
            Settings::set('seo_title_suffix',   trim($_POST['seo_title_suffix']   ?? ''));
            Settings::set('seo_meta_desc',       trim($_POST['seo_meta_desc']       ?? ''));
            Settings::set('seo_og_image',        trim($_POST['seo_og_image']        ?? ''));
            $success[] = 'seo';
        }
    }
}

// Load current values
$homepageId    = (int) Settings::get('homepage_id', 0);
$allPages      = CMS::getPages(['status' => 'published', 'order_by' => 'title ASC']);
$siteName      = Settings::get('site_name',          '');
$siteTagline   = Settings::get('site_tagline',       '');
$footerText    = Settings::get('footer_text',        '');
$blogSlug      = Settings::get('blog_slug',          'blog');
$gaId          = Settings::get('ga_id',              '');
$seoSuffix     = Settings::get('seo_title_suffix',   '');
$seoMetaDesc   = Settings::get('seo_meta_desc',      '');
$seoOgImage    = Settings::get('seo_og_image',       '');

$googleFontsRaw = Settings::get('google_fonts', '[]');
$googleFonts    = json_decode($googleFontsRaw, true);
if (!is_array($googleFonts)) {
    $googleFonts = [];
}

$csrf = Auth::generateCsrf();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings &mdash; SquareStack CMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body class="bg-gray-100">

<div class="flex min-h-screen w-full">

<?php $currentPage = 'settings'; ?>
    <!-- Sidebar -->
    <aside class="w-64 flex-shrink-0 flex flex-col sticky top-0 h-screen overflow-hidden" style="background-color: #0f172a;">
        <!-- Logo -->
        <div class="px-6 py-5 border-b border-slate-700">
            <a href="<?= SITE_URL ?>/admin/" class="flex items-center gap-3 text-white no-underline">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background: linear-gradient(135deg, #6366f1, #8b5cf6);">
                    <i class="fa-solid fa-cube text-white text-sm"></i>
                </div>
                <span class="font-bold text-lg tracking-tight">SquareStack</span>
            </a>
        </div>

        <!-- Nav -->
        <nav class="flex-1 px-3 py-4 space-y-1">
            <?php
            $navItems = [
                ['href' => SITE_URL . '/admin/',            'icon' => 'fa-gauge-high',  'label' => 'Dashboard', 'key' => 'dashboard'],
                ['href' => SITE_URL . '/admin/pages.php',   'icon' => 'fa-file-lines',  'label' => 'Pages',     'key' => 'pages'],
                ['href' => SITE_URL . '/admin/posts.php',   'icon' => 'fa-newspaper',   'label' => 'Posts',     'key' => 'posts'],
                ['href' => SITE_URL . '/admin/menus.php',   'icon' => 'fa-bars',        'label' => 'Menus',     'key' => 'menus'],
                ['href' => SITE_URL . '/admin/media.php',   'icon' => 'fa-photo-film',  'label' => 'Media',     'key' => 'media'],
                ['href' => SITE_URL . '/admin/settings.php','icon' => 'fa-gear',        'label' => 'Settings',  'key' => 'settings'],
            ];
            foreach ($navItems as $item):
                $isActive = ($currentPage === $item['key']);
                $baseClass = 'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-150 no-underline';
                $activeClass = $isActive
                    ? 'bg-indigo-600 text-white'
                    : 'text-slate-400 hover:bg-slate-800 hover:text-white';
            ?>
            <a href="<?= htmlspecialchars($item['href']) ?>" class="<?= $baseClass . ' ' . $activeClass ?>">
                <i class="fa-solid <?= $item['icon'] ?> w-4 text-center"></i>
                <span><?= $item['label'] ?></span>
            </a>
            <?php endforeach; ?>
        </nav>

        <!-- User + Logout -->
        <div class="px-3 py-4 border-t border-slate-700">
            <div class="flex items-center gap-3 px-3 py-2 mb-2">
                <div class="w-8 h-8 rounded-full bg-indigo-500 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                    <?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?>
                </div>
                <div class="overflow-hidden">
                    <p class="text-white text-sm font-medium truncate"><?= htmlspecialchars($user['name'] ?? '') ?></p>
                    <p class="text-slate-400 text-xs truncate"><?= htmlspecialchars($user['role'] ?? '') ?></p>
                </div>
            </div>
            <a href="<?= SITE_URL ?>/admin/logout.php"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-400 hover:bg-slate-800 hover:text-red-400 transition-colors duration-150 no-underline">
                <i class="fa-solid fa-right-from-bracket w-4 text-center"></i>
                <span>Logout</span>
            </a>
        </div>
    </aside>

<!-- Main -->
<div class="flex-1 flex flex-col min-h-screen">

    <!-- Top bar -->
    <header class="bg-white border-b border-gray-200 px-8 py-4 flex items-center justify-between sticky top-0 z-20">
        <div>
            <h1 class="text-xl font-semibold text-gray-800">Settings</h1>
            <p class="text-sm text-gray-500 mt-0.5">Configure your site preferences</p>
        </div>
    </header>

    <main class="flex-1 px-8 py-8 max-w-3xl">

        <?php if (!empty($errors)): ?>
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 rounded-xl px-5 py-4 text-sm flex items-start gap-3">
                <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
                <div><?= htmlspecialchars($errors[0]) ?></div>
            </div>
        <?php endif; ?>

        <!-- ===== General ===== -->
        <form method="post" action="<?= SITE_URL ?>/admin/settings.php#general" class="mb-8">
            <input type="hidden" name="csrf"    value="<?= htmlspecialchars($csrf) ?>">
            <input type="hidden" name="section" value="general">
            <fieldset class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <legend class="sr-only">General</legend>
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-globe text-indigo-500"></i>
                        <span class="font-semibold text-gray-800">General</span>
                    </div>
                    <?php if (in_array('general', $success, true)): ?>
                        <span id="general" class="text-xs text-green-600 font-medium flex items-center gap-1">
                            <i class="fa-solid fa-check"></i> Saved
                        </span>
                    <?php endif; ?>
                </div>
                <div class="px-6 py-5 space-y-5">
                    <div>
                        <label for="site_name" class="block text-sm font-medium text-gray-700 mb-1">Site Name</label>
                        <input type="text" id="site_name" name="site_name"
                               value="<?= htmlspecialchars($siteName) ?>"
                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                               placeholder="My Awesome Site">
                    </div>
                    <div>
                        <label for="site_tagline" class="block text-sm font-medium text-gray-700 mb-1">Site Tagline</label>
                        <input type="text" id="site_tagline" name="site_tagline"
                               value="<?= htmlspecialchars($siteTagline) ?>"
                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                               placeholder="Just another great website">
                    </div>
                    <div>
                        <label for="footer_text" class="block text-sm font-medium text-gray-700 mb-1">Footer Text</label>
                        <input type="text" id="footer_text" name="footer_text"
                               value="<?= htmlspecialchars($footerText) ?>"
                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                               placeholder="&copy; 2025 My Site. All rights reserved.">
                        <p class="text-xs text-gray-400 mt-1">Displayed in the site footer. HTML is allowed.</p>
                    </div>
                </div>
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end">
                    <button type="submit"
                            class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                        <i class="fa-solid fa-floppy-disk"></i> Save General
                    </button>
                </div>
            </fieldset>
        </form>

        <!-- ===== Reading ===== -->
        <form method="post" action="<?= SITE_URL ?>/admin/settings.php#reading" class="mb-8">
            <input type="hidden" name="csrf"    value="<?= htmlspecialchars($csrf) ?>">
            <input type="hidden" name="section" value="reading">
            <fieldset class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <legend class="sr-only">Reading</legend>
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-house text-indigo-500"></i>
                        <span class="font-semibold text-gray-800">Reading</span>
                    </div>
                    <?php if (in_array('reading', $success, true)): ?>
                        <span id="reading" class="text-xs text-green-600 font-medium flex items-center gap-1">
                            <i class="fa-solid fa-check"></i> Saved
                        </span>
                    <?php endif; ?>
                </div>
                <div class="px-6 py-5">
                    <label for="homepage_id" class="block text-sm font-medium text-gray-700 mb-1">Homepage</label>
                    <select id="homepage_id" name="homepage_id"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="0" <?= $homepageId === 0 ? 'selected' : '' ?>>— Your latest posts (default) —</option>
                        <?php foreach ($allPages as $pg): ?>
                        <option value="<?= (int)$pg['id'] ?>" <?= $homepageId === (int)$pg['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($pg['title'] ?? '') ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="text-xs text-gray-400 mt-1">Set a static page as the front page of your site, or leave as default to show the blog index.</p>
                </div>
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end">
                    <button type="submit"
                            class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                        <i class="fa-solid fa-floppy-disk"></i> Save Reading
                    </button>
                </div>
            </fieldset>
        </form>

        <!-- ===== Blog ===== -->
        <form method="post" action="<?= SITE_URL ?>/admin/settings.php#blog" class="mb-8">
            <input type="hidden" name="csrf"    value="<?= htmlspecialchars($csrf) ?>">
            <input type="hidden" name="section" value="blog">
            <fieldset class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <legend class="sr-only">Blog</legend>
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-newspaper text-indigo-500"></i>
                        <span class="font-semibold text-gray-800">Blog</span>
                    </div>
                    <?php if (in_array('blog', $success, true)): ?>
                        <span id="blog" class="text-xs text-green-600 font-medium flex items-center gap-1">
                            <i class="fa-solid fa-check"></i> Saved
                        </span>
                    <?php endif; ?>
                </div>
                <div class="px-6 py-5">
                    <div>
                        <label for="blog_slug" class="block text-sm font-medium text-gray-700 mb-1">Blog Slug</label>
                        <div class="flex items-center gap-0">
                            <span class="px-3 py-2 text-sm text-gray-500 bg-gray-50 border border-r-0 border-gray-300 rounded-l-lg"><?= rtrim(SITE_URL, '/') ?>/</span>
                            <input type="text" id="blog_slug" name="blog_slug"
                                   value="<?= htmlspecialchars($blogSlug) ?>"
                                   class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-r-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                   placeholder="blog">
                        </div>
                        <p class="text-xs text-gray-400 mt-1">The URL prefix for the blog archive page (e.g. <code class="bg-gray-100 px-1 rounded">blog</code>).</p>
                    </div>
                </div>
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end">
                    <button type="submit"
                            class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                        <i class="fa-solid fa-floppy-disk"></i> Save Blog
                    </button>
                </div>
            </fieldset>
        </form>

        <!-- ===== Google Fonts ===== -->
        <form method="post" action="<?= SITE_URL ?>/admin/settings.php#google_fonts" class="mb-8">
            <input type="hidden" name="csrf"    value="<?= htmlspecialchars($csrf) ?>">
            <input type="hidden" name="section" value="google_fonts">
            <fieldset class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <legend class="sr-only">Google Fonts</legend>
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="fa-brands fa-google text-indigo-500"></i>
                        <span class="font-semibold text-gray-800">Google Fonts</span>
                    </div>
                    <?php if (in_array('google_fonts', $success, true)): ?>
                        <span id="google_fonts" class="text-xs text-green-600 font-medium flex items-center gap-1">
                            <i class="fa-solid fa-check"></i> Saved
                        </span>
                    <?php endif; ?>
                </div>
                <div class="px-6 py-5">
                    <p class="text-xs text-gray-500 mb-4">Each font entry will be loaded via the Google Fonts API. Specify the family name exactly as it appears on <a href="https://fonts.google.com" target="_blank" class="text-indigo-500 hover:underline">fonts.google.com</a> and a comma-separated list of weights.</p>

                    <div class="space-y-1 mb-1">
                        <div class="grid grid-cols-[1fr_auto_auto] gap-2 text-xs font-medium text-gray-500 uppercase tracking-wide px-1">
                            <span>Family Name</span>
                            <span class="w-40">Weights (comma-separated)</span>
                            <span class="w-8"></span>
                        </div>
                    </div>

                    <div id="fontRows" class="space-y-2">
                        <?php if (empty($googleFonts)): ?>
                            <!-- empty placeholder row rendered by JS -->
                        <?php else: ?>
                            <?php foreach ($googleFonts as $font): ?>
                                <div class="font-row grid grid-cols-[1fr_auto_auto] gap-2 items-center">
                                    <input type="text" name="font_family[]"
                                           value="<?= htmlspecialchars($font['family'] ?? '') ?>"
                                           placeholder="e.g. Inter"
                                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    <input type="text" name="font_weights[]"
                                           value="<?= htmlspecialchars($font['weights'] ?? '400') ?>"
                                           placeholder="400,700"
                                           class="w-40 px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    <button type="button" onclick="removeFontRow(this)"
                                            class="w-8 h-9 flex items-center justify-center text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition">
                                        <i class="fa-solid fa-xmark text-sm"></i>
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <button type="button" id="addFontRow"
                            class="mt-4 inline-flex items-center gap-2 text-sm text-indigo-600 hover:text-indigo-800 font-medium transition">
                        <i class="fa-solid fa-plus text-xs"></i> Add Font
                    </button>
                </div>
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end">
                    <button type="submit"
                            class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                        <i class="fa-solid fa-floppy-disk"></i> Save Fonts
                    </button>
                </div>
            </fieldset>
        </form>

        <!-- ===== Analytics ===== -->
        <form method="post" action="<?= SITE_URL ?>/admin/settings.php#analytics" class="mb-8">
            <input type="hidden" name="csrf"    value="<?= htmlspecialchars($csrf) ?>">
            <input type="hidden" name="section" value="analytics">
            <fieldset class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <legend class="sr-only">Analytics</legend>
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-chart-line text-indigo-500"></i>
                        <span class="font-semibold text-gray-800">Analytics</span>
                    </div>
                    <?php if (in_array('analytics', $success, true)): ?>
                        <span id="analytics" class="text-xs text-green-600 font-medium flex items-center gap-1">
                            <i class="fa-solid fa-check"></i> Saved
                        </span>
                    <?php endif; ?>
                </div>
                <div class="px-6 py-5">
                    <div>
                        <label for="ga_id" class="block text-sm font-medium text-gray-700 mb-1">Google Analytics ID</label>
                        <input type="text" id="ga_id" name="ga_id"
                               value="<?= htmlspecialchars($gaId) ?>"
                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                               placeholder="G-XXXXXXXXXX">
                        <p class="text-xs text-gray-400 mt-1">Your Google Analytics 4 Measurement ID (starts with <code class="bg-gray-100 px-1 rounded">G-</code>). Leave blank to disable tracking.</p>
                    </div>
                </div>
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end">
                    <button type="submit"
                            class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                        <i class="fa-solid fa-floppy-disk"></i> Save Analytics
                    </button>
                </div>
            </fieldset>
        </form>

        <!-- ===== SEO Defaults ===== -->
        <form method="post" action="<?= SITE_URL ?>/admin/settings.php#seo" class="mb-8">
            <input type="hidden" name="csrf"    value="<?= htmlspecialchars($csrf) ?>">
            <input type="hidden" name="section" value="seo">
            <fieldset class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <legend class="sr-only">SEO Defaults</legend>
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-magnifying-glass-chart text-indigo-500"></i>
                        <span class="font-semibold text-gray-800">SEO Defaults</span>
                    </div>
                    <?php if (in_array('seo', $success, true)): ?>
                        <span id="seo" class="text-xs text-green-600 font-medium flex items-center gap-1">
                            <i class="fa-solid fa-check"></i> Saved
                        </span>
                    <?php endif; ?>
                </div>
                <div class="px-6 py-5 space-y-5">
                    <div>
                        <label for="seo_title_suffix" class="block text-sm font-medium text-gray-700 mb-1">Default Title Suffix</label>
                        <input type="text" id="seo_title_suffix" name="seo_title_suffix"
                               value="<?= htmlspecialchars($seoSuffix) ?>"
                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                               placeholder="&mdash; My Site Name">
                        <p class="text-xs text-gray-400 mt-1">Appended to each page title. Example: <code class="bg-gray-100 px-1 rounded">Page Title &mdash; My Site</code>.</p>
                    </div>
                    <div>
                        <label for="seo_meta_desc" class="block text-sm font-medium text-gray-700 mb-1">Default Meta Description</label>
                        <textarea id="seo_meta_desc" name="seo_meta_desc" rows="3"
                                  class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 resize-none"
                                  placeholder="A short description of your site used when a page has no specific meta description."><?= htmlspecialchars($seoMetaDesc) ?></textarea>
                        <p class="text-xs text-gray-400 mt-1">Recommended length: 150&ndash;160 characters. <span id="metaDescCount" class="font-medium"></span></p>
                    </div>
                    <div>
                        <label for="seo_og_image" class="block text-sm font-medium text-gray-700 mb-1">Default OG Image</label>
                        <div class="flex gap-2">
                            <input type="text" id="seo_og_image" name="seo_og_image"
                                   value="<?= htmlspecialchars($seoOgImage) ?>"
                                   class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                   placeholder="https://example.com/og-image.jpg">
                            <button type="button" id="browseOgImage"
                                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 border border-gray-300 rounded-lg transition whitespace-nowrap">
                                <i class="fa-solid fa-folder-open text-xs"></i> Browse
                            </button>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">Fallback image used for social sharing when a post/page has no OG image. Recommended: 1200&times;630 px.</p>
                        <div id="ogImagePreview" class="mt-3 <?= $seoOgImage ? '' : 'hidden' ?>">
                            <img src="<?= htmlspecialchars($seoOgImage) ?>" alt="OG image preview"
                                 class="h-24 rounded-lg object-cover border border-gray-200"
                                 onerror="this.parentElement.classList.add('hidden')">
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end">
                    <button type="submit"
                            class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                        <i class="fa-solid fa-floppy-disk"></i> Save SEO
                    </button>
                </div>
            </fieldset>
        </form>

    </main>
</div>

<!-- Media Browser Modal -->
<div id="mediaBrowserModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl mx-4 flex flex-col" style="max-height:85vh;">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
            <h3 class="text-base font-semibold text-gray-900">Select Image</h3>
            <button type="button" onclick="closeMediaBrowser()"
                    class="text-gray-400 hover:text-gray-700 transition">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-6">
            <div id="mediaBrowserGrid" class="grid grid-cols-4 gap-3">
                <div class="col-span-4 text-center py-10 text-gray-400 text-sm">
                    <i class="fa-solid fa-spinner fa-spin text-2xl mb-3"></i><br>Loading media&hellip;
                </div>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-gray-200 flex justify-end">
            <button type="button" onclick="closeMediaBrowser()"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
                Cancel
            </button>
        </div>
    </div>
</div>

<script>
/* ---- Google Fonts dynamic rows ---- */
function addFontRow(family, weights) {
    const container = document.getElementById('fontRows');
    const row = document.createElement('div');
    row.className = 'font-row grid grid-cols-[1fr_auto_auto] gap-2 items-center';
    row.innerHTML =
        '<input type="text" name="font_family[]" value="' + escHtml(family || '') + '" placeholder="e.g. Inter"' +
        ' class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">' +
        '<input type="text" name="font_weights[]" value="' + escHtml(weights || '400') + '" placeholder="400,700"' +
        ' class="w-40 px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">' +
        '<button type="button" onclick="removeFontRow(this)"' +
        ' class="w-8 h-9 flex items-center justify-center text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition">' +
        '<i class="fa-solid fa-xmark text-sm"></i></button>';
    container.appendChild(row);
    row.querySelector('input').focus();
}

function removeFontRow(btn) {
    btn.closest('.font-row').remove();
}

document.getElementById('addFontRow').addEventListener('click', function () {
    addFontRow('', '400');
});

function escHtml(str) {
    return str.replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}

/* ---- Meta description character counter ---- */
(function () {
    const ta    = document.getElementById('seo_meta_desc');
    const counter = document.getElementById('metaDescCount');
    function update() {
        const len = ta.value.length;
        counter.textContent = len + ' characters';
        counter.className   = 'font-medium ' + (len > 160 ? 'text-red-600' : len >= 130 ? 'text-yellow-600' : 'text-gray-500');
    }
    ta.addEventListener('input', update);
    update();
})();

/* ---- OG Image preview on URL input ---- */
(function () {
    const input   = document.getElementById('seo_og_image');
    const preview = document.getElementById('ogImagePreview');
    const img     = preview ? preview.querySelector('img') : null;
    if (!input || !img) return;
    input.addEventListener('change', function () {
        const url = this.value.trim();
        if (url) {
            img.src = url;
            preview.classList.remove('hidden');
        } else {
            preview.classList.add('hidden');
        }
    });
})();

/* ---- Media browser ---- */
var _mediaCallback = null;

function openMediaBrowser(callback) {
    _mediaCallback = callback;
    const modal = document.getElementById('mediaBrowserModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    loadMediaBrowser();
}

function closeMediaBrowser() {
    const modal = document.getElementById('mediaBrowserModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    _mediaCallback = null;
}

function loadMediaBrowser() {
    const grid = document.getElementById('mediaBrowserGrid');
    grid.innerHTML = '<div class="col-span-4 text-center py-10 text-gray-400 text-sm"><i class="fa-solid fa-spinner fa-spin text-2xl mb-3"></i><br>Loading media&hellip;</div>';

    fetch('<?= SITE_URL ?>/admin/ajax.php?action=list_media')
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (!data.files || data.files.length === 0) {
                grid.innerHTML = '<div class="col-span-4 text-center py-10 text-gray-400 text-sm"><i class="fa-solid fa-photo-film text-3xl mb-3 opacity-40"></i><br>No media found. Upload images in the <a href="<?= SITE_URL ?>/admin/media.php" class="text-indigo-500 hover:underline">Media</a> section.</div>';
                return;
            }
            grid.innerHTML = '';
            data.files.forEach(function (file) {
                if (!file.mime || !file.mime.startsWith('image/')) return;
                var url = file.url || '';
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'group relative rounded-lg overflow-hidden border-2 border-transparent hover:border-indigo-500 focus:outline-none focus:border-indigo-500 transition aspect-square bg-gray-100';
                btn.innerHTML = '<img src="' + escHtml(url) + '" alt="" class="w-full h-full object-cover group-hover:opacity-90 transition">'
                              + '<div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition flex items-end p-1">'
                              + '<span class="text-white text-xs truncate bg-black/40 rounded px-1 w-full opacity-0 group-hover:opacity-100 transition">' + escHtml(file.name || '') + '</span></div>';
                btn.addEventListener('click', function () {
                    if (_mediaCallback) _mediaCallback(url);
                    closeMediaBrowser();
                });
                grid.appendChild(btn);
            });
            if (grid.children.length === 0) {
                grid.innerHTML = '<div class="col-span-4 text-center py-10 text-gray-400 text-sm">No images found.</div>';
            }
        })
        .catch(function () {
            grid.innerHTML = '<div class="col-span-4 text-center py-10 text-red-400 text-sm"><i class="fa-solid fa-triangle-exclamation mr-1"></i> Failed to load media.</div>';
        });
}

document.getElementById('browseOgImage').addEventListener('click', function () {
    openMediaBrowser(function (url) {
        var input   = document.getElementById('seo_og_image');
        var preview = document.getElementById('ogImagePreview');
        var img     = preview ? preview.querySelector('img') : null;
        input.value = url;
        if (img) {
            img.src = url;
            preview.classList.remove('hidden');
        }
    });
});

document.getElementById('mediaBrowserModal').addEventListener('click', function (e) {
    if (e.target === this) closeMediaBrowser();
});

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeMediaBrowser();
});
</script>

</div><!-- /.flex.min-h-screen -->
</body>
</html>
