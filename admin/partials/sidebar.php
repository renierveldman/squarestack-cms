<?php
// Accepts $currentPage (string) and $user (array) from the including file.
// Falls back to $currentUser if $user is not set (users.php / users-edit.php).
$_sidebarUser = $user ?? $currentUser ?? [];
?>
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
    <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
        <?php
        $navItems = [
            ['href' => SITE_URL . '/admin/',                  'icon' => 'fa-gauge-high', 'label' => 'Dashboard',     'key' => 'dashboard'],
            ['href' => SITE_URL . '/admin/pages.php',         'icon' => 'fa-file-lines', 'label' => 'Pages',         'key' => 'pages'],
            ['href' => SITE_URL . '/admin/posts.php',         'icon' => 'fa-newspaper',  'label' => 'Posts',         'key' => 'posts'],
            ['href' => SITE_URL . '/admin/menus.php',         'icon' => 'fa-bars',       'label' => 'Menus',         'key' => 'menus'],
            ['href' => SITE_URL . '/admin/media.php',         'icon' => 'fa-photo-film', 'label' => 'Media',         'key' => 'media'],
            ['href' => SITE_URL . '/admin/users.php',         'icon' => 'fa-users',      'label' => 'Users',         'key' => 'users'],
            ['href' => SITE_URL . '/admin/theme-options.php', 'icon' => 'fa-palette',    'label' => 'Theme Options', 'key' => 'theme_options'],
            ['href' => SITE_URL . '/admin/settings.php',      'icon' => 'fa-gear',       'label' => 'Settings',      'key' => 'settings'],
        ];
        foreach ($navItems as $item):
            $isActive    = (($currentPage ?? '') === $item['key']);
            $baseClass   = 'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-150 no-underline';
            $activeClass = $isActive ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white';
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
            <?php if (!empty($_sidebarUser['avatar'])): ?>
            <img src="<?= htmlspecialchars($_sidebarUser['avatar']) ?>" alt=""
                 class="w-8 h-8 rounded-full object-cover flex-shrink-0">
            <?php else: ?>
            <div class="w-8 h-8 rounded-full bg-indigo-500 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                <?= strtoupper(substr($_sidebarUser['name'] ?? 'U', 0, 1)) ?>
            </div>
            <?php endif; ?>
            <div class="overflow-hidden">
                <p class="text-white text-sm font-medium truncate"><?= htmlspecialchars($_sidebarUser['name'] ?? '') ?></p>
                <p class="text-slate-400 text-xs truncate"><?= htmlspecialchars($_sidebarUser['role'] ?? '') ?></p>
            </div>
        </div>
        <a href="<?= SITE_URL ?>/admin/logout.php"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-400 hover:bg-slate-800 hover:text-red-400 transition-colors duration-150 no-underline">
            <i class="fa-solid fa-right-from-bracket w-4 text-center"></i>
            <span>Logout</span>
        </a>
    </div>

</aside>
