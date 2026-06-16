<?php
global $page, $post;
$navCta     = get_theme_option('nav_cta_text') ?: 'Get in Touch';
$navCtaUrl  = get_theme_option('nav_cta_url')  ?: site_url('/contact');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php seo_head(); ?>
    <?php integrations_head(); ?>
    <?php google_fonts_head(); ?>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            content: [],
            theme: {
                extend: {
                    colors: {
                        brand: {
                            black: '#0a0a0a',
                            dark:  '#111111',
                        },
                    },
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                    },
                },
            },
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="bg-white text-gray-900 font-sans antialiased">
<?php integrations_body_open(); ?>

<header class="sticky top-0 z-50 bg-white border-b border-gray-100 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">

            <!-- Logo -->
            <div class="flex-shrink-0">
                <?php $logo = get_theme_option('logo'); ?>
                <a href="<?= site_url('/') ?>" class="flex items-center no-underline">
                    <?php if ($logo): ?>
                        <img src="<?= esc_url($logo) ?>" alt="<?= esc_attr(get_site('site_name')) ?>" class="h-8 w-auto">
                    <?php else: ?>
                        <img src="<?= site_url('/admin/img/logo1x.svg') ?>" alt="<?= esc_attr(get_site('site_name')) ?>" class="h-8 w-auto">
                    <?php endif; ?>
                </a>
            </div>

            <!-- Desktop nav -->
            <nav class="hidden md:flex items-center gap-8">
                <?php
                $menu      = get_menu('primary');
                $currentUri = strtok($_SERVER['REQUEST_URI'], '?');
                if (!empty($menu['items'])):
                    foreach ($menu['items'] as $item):
                        $isActive = rtrim($currentUri, '/') === rtrim(parse_url($item['url'], PHP_URL_PATH), '/');
                        $target   = (!empty($item['target']) && $item['target'] === '_blank') ? ' target="_blank" rel="noopener"' : '';
                ?>
                <a href="<?= esc_url($item['url']) ?>"<?= $target ?>
                   class="text-sm font-medium transition-colors <?= $isActive ? 'text-gray-900' : 'text-gray-500 hover:text-gray-900' ?>">
                    <?= esc_html($item['label']) ?>
                </a>
                <?php endforeach; endif; ?>
            </nav>

            <!-- CTA + hamburger -->
            <div class="flex items-center gap-3">
                <a href="<?= esc_url($navCtaUrl) ?>"
                   class="hidden md:inline-flex items-center gap-1.5 bg-black text-white text-sm font-semibold px-4 py-2 rounded-lg hover:bg-gray-800 transition-colors">
                    <?= esc_html($navCta) ?> <span class="text-xs">&rarr;</span>
                </a>
                <button id="mobile-menu-button" type="button" aria-controls="mobile-menu" aria-expanded="false"
                        class="md:hidden inline-flex items-center justify-center p-2 rounded-lg text-gray-600 hover:bg-gray-100 transition">
                    <span class="sr-only">Open menu</span>
                    <i class="fa-solid fa-bars text-lg"></i>
                </button>
            </div>

        </div>
    </div>

    <!-- Mobile menu -->
    <div id="mobile-menu" class="hidden md:hidden border-t border-gray-100 bg-white">
        <div class="px-4 py-4 space-y-1">
            <?php if (!empty($menu['items'])): foreach ($menu['items'] as $item):
                $isActive = rtrim(strtok($_SERVER['REQUEST_URI'], '?'), '/') === rtrim(parse_url($item['url'], PHP_URL_PATH), '/');
                $target   = (!empty($item['target']) && $item['target'] === '_blank') ? ' target="_blank" rel="noopener"' : '';
            ?>
            <a href="<?= esc_url($item['url']) ?>"<?= $target ?>
               class="block px-3 py-2 rounded-lg text-sm font-medium <?= $isActive ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' ?> transition">
                <?= esc_html($item['label']) ?>
            </a>
            <?php endforeach; endif; ?>
            <a href="<?= esc_url($navCtaUrl) ?>"
               class="block mt-2 px-3 py-2 rounded-lg text-sm font-semibold bg-black text-white text-center hover:bg-gray-800 transition">
                <?= esc_html($navCta) ?> &rarr;
            </a>
        </div>
    </div>
</header>

<script>
(function () {
    var btn  = document.getElementById('mobile-menu-button');
    var menu = document.getElementById('mobile-menu');
    if (!btn || !menu) return;
    btn.addEventListener('click', function () {
        var open = menu.classList.toggle('hidden') === false;
        btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
}());
</script>
