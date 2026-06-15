<?php
/**
 * Header Template
 *
 * Included at the top of every template.
 * Uses global $page, $post.
 */

global $page, $post;
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
                        primary: {
                            DEFAULT: '#6366f1',
                            50:  '#eef2ff',
                            100: '#e0e7ff',
                            200: '#c7d2fe',
                            300: '#a5b4fc',
                            400: '#818cf8',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            800: '#3730a3',
                            900: '#312e81',
                            950: '#1e1b4b',
                        },
                    },
                },
            },
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="bg-white text-gray-900">
<?php integrations_body_open(); ?>

<header class="sticky top-0 z-50 bg-white border-b border-gray-200 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">

            <!-- Logo / Site Name -->
            <div class="flex-shrink-0">
                <a href="<?php echo site_url('/'); ?>" class="text-xl font-bold text-indigo-600 hover:text-indigo-700 transition-colors">
                    <?php echo get_site('site_name'); ?>
                </a>
            </div>

            <!-- Primary Navigation (desktop) -->
            <nav class="hidden md:flex items-center">
                <?php render_menu('primary', 'flex gap-6', '', 'text-gray-700 hover:text-indigo-600 font-medium transition-colors'); ?>
            </nav>
        

            <!-- Hamburger Button (mobile) -->
            <button
                id="mobile-menu-button"
                type="button"
                class="md:hidden inline-flex items-center justify-center p-2 rounded-md text-gray-700 hover:text-indigo-600 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500 transition-colors"
                aria-controls="mobile-menu"
                aria-expanded="false"
            >
                <span class="sr-only">Open main menu</span>
                <i class="fa-solid fa-bars text-xl"></i>
            </button>

        </div>
    </div>

    <!-- Mobile Menu Drawer -->
    <div id="mobile-menu" class="hidden md:hidden border-t border-gray-200 bg-white">
        <div class="px-4 py-3 space-y-1">
            <?php render_menu('primary', 'flex flex-col gap-1', '', 'block px-3 py-2 rounded-md text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 font-medium transition-colors'); ?>
        </div>
    </div>
</header>

<script>
(function () {
    var button = document.getElementById('mobile-menu-button');
    var menu   = document.getElementById('mobile-menu');

    if (!button || !menu) { return; }

    button.addEventListener('click', function () {
        var isOpen = menu.classList.toggle('hidden') === false;
        button.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });
}());
</script>
