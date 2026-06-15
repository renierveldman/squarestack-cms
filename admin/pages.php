<?php
require_once dirname(__DIR__) . '/config.php';
require_once CORE_PATH . '/Database.php';
require_once CORE_PATH . '/Cache.php';
require_once CORE_PATH . '/Auth.php';
require_once CORE_PATH . '/CMS.php';

Auth::require();

$user  = Auth::currentUser();
$pages = CMS::getPages(['order_by' => 'updated_at DESC']);
$csrf  = Auth::generateCsrf();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pages &mdash; SquareStack CMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen flex">

    <!-- Sidebar -->
    <aside class="w-64 bg-gray-900 text-gray-200 flex flex-col min-h-screen flex-shrink-0">
        <div class="px-6 py-5 border-b border-gray-700">
            <span class="text-xl font-bold text-white">SquareStack</span>
            <span class="block text-xs text-gray-400 mt-0.5">Content Management</span>
        </div>
        <nav class="flex-1 px-4 py-6 space-y-1">
            <a href="<?= SITE_URL ?>/admin/dashboard.php"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-gray-300 hover:bg-gray-700 hover:text-white transition-colors">
                <i class="fa-solid fa-gauge-high w-4 text-center"></i> Dashboard
            </a>
            <a href="<?= SITE_URL ?>/admin/pages.php"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm bg-gray-700 text-white font-medium">
                <i class="fa-solid fa-file-lines w-4 text-center"></i> Pages
            </a>
            <a href="<?= SITE_URL ?>/admin/posts.php"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-gray-300 hover:bg-gray-700 hover:text-white transition-colors">
                <i class="fa-solid fa-newspaper w-4 text-center"></i> Posts
            </a>
            <a href="<?= SITE_URL ?>/admin/media.php"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-gray-300 hover:bg-gray-700 hover:text-white transition-colors">
                <i class="fa-solid fa-images w-4 text-center"></i> Media
            </a>
            <a href="<?= SITE_URL ?>/admin/settings.php"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-gray-300 hover:bg-gray-700 hover:text-white transition-colors">
                <i class="fa-solid fa-gear w-4 text-center"></i> Settings
            </a>
        </nav>
        <div class="px-4 py-4 border-t border-gray-700">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-8 h-8 rounded-full bg-indigo-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                    <?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?>
                </div>
                <div class="min-w-0">
                    <p class="text-sm text-white truncate"><?= htmlspecialchars($user['name'] ?? '') ?></p>
                    <p class="text-xs text-gray-400 truncate"><?= htmlspecialchars($user['role'] ?? '') ?></p>
                </div>
            </div>
            <a href="<?= SITE_URL ?>/admin/logout.php"
               class="flex items-center gap-2 text-xs text-gray-400 hover:text-white transition-colors">
                <i class="fa-solid fa-right-from-bracket"></i> Sign out
            </a>
        </div>
    </aside>

    <!-- Main content -->
    <div class="flex-1 flex flex-col min-w-0">

        <!-- Top bar -->
        <header class="bg-white border-b border-gray-200 px-8 py-4 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold text-gray-800">Pages</h1>
                <p class="text-sm text-gray-500 mt-0.5"><?= count($pages) ?> page<?= count($pages) !== 1 ? 's' : '' ?> total</p>
            </div>
            <a href="<?= SITE_URL ?>/admin/pages-edit.php"
               class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                <i class="fa-solid fa-plus"></i> Add New Page
            </a>
        </header>

        <!-- Content area -->
        <main class="flex-1 px-8 py-8">

            <?php if (isset($_GET['deleted']) && $_GET['deleted'] === '1'): ?>
            <div class="mb-6 flex items-center gap-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg px-4 py-3">
                <i class="fa-solid fa-circle-check"></i>
                Page deleted successfully.
            </div>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
            <div class="mb-6 flex items-center gap-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg px-4 py-3">
                <i class="fa-solid fa-circle-exclamation"></i>
                <?= htmlspecialchars($_GET['error']) ?>
            </div>
            <?php endif; ?>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <?php if (empty($pages)): ?>
                <div class="text-center py-16 text-gray-400">
                    <i class="fa-regular fa-file-lines text-4xl mb-3"></i>
                    <p class="text-sm">No pages yet. <a href="<?= SITE_URL ?>/admin/pages-edit.php" class="text-indigo-600 hover:underline">Create your first page.</a></p>
                </div>
                <?php else: ?>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Title</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Slug</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Template</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Updated</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        <?php foreach ($pages as $page): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <span class="font-medium text-gray-900 text-sm">
                                    <?= htmlspecialchars($page['title'] ?? '') ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <code class="text-xs text-gray-500 bg-gray-100 px-2 py-0.5 rounded">
                                    /<?= htmlspecialchars($page['slug'] ?? '') ?>
                                </code>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                <?= htmlspecialchars($page['template'] ?? 'default') ?>
                            </td>
                            <td class="px-6 py-4">
                                <?php if (($page['status'] ?? '') === 'published'): ?>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                    Published
                                </span>
                                <?php else: ?>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                                    Draft
                                </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                <?php
                                $updated = $page['updated_at'] ?? null;
                                echo $updated ? date('M j, Y', strtotime($updated)) : '&mdash;';
                                ?>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-3">
                                    <a href="<?= SITE_URL ?>/admin/pages-edit.php?id=<?= (int) $page['id'] ?>"
                                       class="text-indigo-600 hover:text-indigo-800 text-sm font-medium transition-colors"
                                       title="Edit">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                        <span class="ml-1">Edit</span>
                                    </a>
                                    <button type="button"
                                            class="text-red-500 hover:text-red-700 text-sm font-medium transition-colors js-delete-page"
                                            data-id="<?= (int) $page['id'] ?>"
                                            data-title="<?= htmlspecialchars($page['title'] ?? '', ENT_QUOTES) ?>"
                                            data-csrf="<?= htmlspecialchars($csrf) ?>"
                                            title="Delete">
                                        <i class="fa-solid fa-trash-can"></i>
                                        <span class="ml-1">Delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>

        </main>
    </div>

    <!-- Delete confirmation modal -->
    <div id="deleteModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4 p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-trash-can text-red-500"></i>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-gray-900">Delete Page</h3>
                    <p class="text-sm text-gray-500">This action cannot be undone.</p>
                </div>
            </div>
            <p class="text-sm text-gray-700 mb-6">
                Are you sure you want to delete <strong id="deletePageTitle" class="text-gray-900"></strong>?
            </p>
            <div class="flex justify-end gap-3">
                <button type="button" id="cancelDelete"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                    Cancel
                </button>
                <form id="deleteForm" method="POST" action="<?= SITE_URL ?>/admin/ajax.php?action=delete_page">
                    <input type="hidden" name="id" id="deletePageId">
                    <input type="hidden" name="csrf_token" id="deleteCsrf">
                    <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors">
                        <i class="fa-solid fa-trash-can mr-1"></i> Delete
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
    (function () {
        const modal       = document.getElementById('deleteModal');
        const titleEl     = document.getElementById('deletePageTitle');
        const idInput     = document.getElementById('deletePageId');
        const csrfInput   = document.getElementById('deleteCsrf');
        const cancelBtn   = document.getElementById('cancelDelete');

        document.querySelectorAll('.js-delete-page').forEach(function (btn) {
            btn.addEventListener('click', function () {
                titleEl.textContent  = btn.dataset.title;
                idInput.value        = btn.dataset.id;
                csrfInput.value      = btn.dataset.csrf;
                modal.classList.remove('hidden');
            });
        });

        cancelBtn.addEventListener('click', function () {
            modal.classList.add('hidden');
        });

        modal.addEventListener('click', function (e) {
            if (e.target === modal) {
                modal.classList.add('hidden');
            }
        });
    })();
    </script>

</body>
</html>
