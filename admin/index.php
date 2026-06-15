<?php
require_once __DIR__ . '/../config.php';
require_once CORE_PATH . '/Database.php';
require_once CORE_PATH . '/Cache.php';
require_once CORE_PATH . '/Auth.php';
require_once CORE_PATH . '/CMS.php';
require_once CORE_PATH . '/Media.php';
require_once CORE_PATH . '/Settings.php';
require_once CORE_PATH . '/Slug.php';
require_once CORE_PATH . '/Router.php';

Auth::require();

$user = Auth::currentUser();
$db   = Database::getInstance();

$totalPages = (int) ($db->fetch('SELECT COUNT(*) AS cnt FROM `pages`')['cnt'] ?? 0);
$totalPosts = (int) ($db->fetch('SELECT COUNT(*) AS cnt FROM `posts`')['cnt'] ?? 0);
$totalMedia = (int) ($db->fetch('SELECT COUNT(*) AS cnt FROM `media`')['cnt'] ?? 0);

$recentPosts = $db->fetchAll(
    'SELECT `id`, `title`, `status`, `created_at` FROM `posts` ORDER BY `created_at` DESC LIMIT 5'
);

$currentPage = 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard &mdash; SquareStack CMS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Google+Sans:wght@400;500;700&family=Google+Sans+Display:wght@400;500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        body { font-family: 'Google Sans', 'Google Sans Display', system-ui, sans-serif; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex">

    <?php require __DIR__ . '/partials/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col min-h-screen">

        <!-- Top Bar -->
        <header class="bg-white shadow-sm border-b border-gray-200 px-8 py-4 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold text-gray-800">Dashboard</h1>
                <p class="text-sm text-gray-500 mt-0.5">Welcome back, <?= htmlspecialchars($user['name'] ?? 'Admin') ?>!</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="<?= SITE_URL ?>/" target="_blank"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors no-underline">
                    <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
                    View Site
                </a>
            </div>
        </header>

        <!-- Page Body -->
        <main class="flex-1 p-8">

            <!-- Stat Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-8">

                <!-- Pages Card -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex items-center gap-5">
                    <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center flex-shrink-0">
                        <i class="fa-solid fa-file-lines text-blue-500 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Total Pages</p>
                        <p class="text-3xl font-bold text-gray-800 mt-0.5"><?= number_format($totalPages) ?></p>
                    </div>
                </div>

                <!-- Posts Card -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex items-center gap-5">
                    <div class="w-12 h-12 rounded-xl bg-violet-50 flex items-center justify-center flex-shrink-0">
                        <i class="fa-solid fa-newspaper text-violet-500 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Total Posts</p>
                        <p class="text-3xl font-bold text-gray-800 mt-0.5"><?= number_format($totalPosts) ?></p>
                    </div>
                </div>

                <!-- Media Card -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex items-center gap-5">
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center flex-shrink-0">
                        <i class="fa-solid fa-photo-film text-emerald-500 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Media Items</p>
                        <p class="text-3xl font-bold text-gray-800 mt-0.5"><?= number_format($totalMedia) ?></p>
                    </div>
                </div>

            </div>

            <!-- Bottom Grid: Recent Posts + Quick Actions -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Recent Posts Table -->
                <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                        <h2 class="text-base font-semibold text-gray-800">Recent Posts</h2>
                        <a href="<?= SITE_URL ?>/admin/posts.php"
                           class="text-sm text-indigo-600 hover:text-indigo-700 font-medium no-underline">View all</a>
                    </div>
                    <div class="overflow-x-auto">
                        <?php if (empty($recentPosts)): ?>
                        <div class="px-6 py-10 text-center text-gray-400">
                            <i class="fa-solid fa-newspaper text-3xl mb-3 block opacity-30"></i>
                            <p class="text-sm">No posts yet.</p>
                        </div>
                        <?php else: ?>
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200">
                                    <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Title</th>
                                    <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                                    <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Date</th>
                                    <th class="px-6 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php foreach ($recentPosts as $post): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-3.5 font-medium text-gray-800 max-w-xs truncate">
                                        <?= htmlspecialchars($post['title'] ?? '(Untitled)') ?>
                                    </td>
                                    <td class="px-6 py-3.5">
                                        <?php
                                        $status = $post['status'] ?? 'draft';
                                        $statusClasses = [
                                            'published' => 'bg-emerald-100 text-emerald-700',
                                            'draft'     => 'bg-amber-100 text-amber-700',
                                            'scheduled' => 'bg-blue-100 text-blue-700',
                                        ];
                                        $cls = $statusClasses[$status] ?? 'bg-gray-100 text-gray-600';
                                        ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $cls ?>">
                                            <?= ucfirst(htmlspecialchars($status)) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-3.5 text-gray-500 whitespace-nowrap">
                                        <?= htmlspecialchars(date('M j, Y', strtotime($post['created_at'] ?? 'now'))) ?>
                                    </td>
                                    <td class="px-6 py-3.5 text-right">
                                        <a href="<?= SITE_URL ?>/admin/posts.php?action=edit&id=<?= (int) $post['id'] ?>"
                                           class="text-indigo-600 hover:text-indigo-700 no-underline text-xs font-medium">
                                            Edit
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-base font-semibold text-gray-800">Quick Actions</h2>
                    </div>
                    <div class="p-6 space-y-3">
                        <a href="<?= SITE_URL ?>/admin/pages.php?action=new"
                           class="flex items-center gap-3 w-full px-4 py-3 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-lg text-sm font-medium transition-colors no-underline">
                            <i class="fa-solid fa-plus w-4 text-center"></i>
                            New Page
                        </a>
                        <a href="<?= SITE_URL ?>/admin/posts.php?action=new"
                           class="flex items-center gap-3 w-full px-4 py-3 bg-violet-50 hover:bg-violet-100 text-violet-700 rounded-lg text-sm font-medium transition-colors no-underline">
                            <i class="fa-solid fa-pen-to-square w-4 text-center"></i>
                            New Post
                        </a>
                        <a href="<?= SITE_URL ?>/admin/media.php?action=upload"
                           class="flex items-center gap-3 w-full px-4 py-3 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded-lg text-sm font-medium transition-colors no-underline">
                            <i class="fa-solid fa-upload w-4 text-center"></i>
                            Upload Media
                        </a>
                        <a href="<?= SITE_URL ?>/admin/menus.php"
                           class="flex items-center gap-3 w-full px-4 py-3 bg-orange-50 hover:bg-orange-100 text-orange-700 rounded-lg text-sm font-medium transition-colors no-underline">
                            <i class="fa-solid fa-bars w-4 text-center"></i>
                            Manage Menus
                        </a>
                        <a href="<?= SITE_URL ?>/admin/settings.php"
                           class="flex items-center gap-3 w-full px-4 py-3 bg-gray-50 hover:bg-gray-100 text-gray-700 rounded-lg text-sm font-medium transition-colors no-underline">
                            <i class="fa-solid fa-gear w-4 text-center"></i>
                            Site Settings
                        </a>
                    </div>
                </div>

            </div>
        </main>

        <footer class="px-8 py-4 text-center text-xs text-gray-400 border-t border-gray-200 bg-white">
            SquareStack CMS &mdash; <?= date('Y') ?>
        </footer>
    </div>

</body>
</html>
