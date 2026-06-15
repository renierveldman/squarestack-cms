<?php
require_once dirname(__DIR__) . '/config.php';
require_once CORE_PATH . '/Database.php';
require_once CORE_PATH . '/Auth.php';

Auth::require();
$user = Auth::currentUser();
$db   = Database::getInstance();

// Redirect to editor for new post
if (($_GET['action'] ?? '') === 'new') {
    header('Location: ' . SITE_URL . '/admin/posts-edit.php');
    exit;
}

// Handle delete
if (isset($_POST['action'], $_POST['id'], $_POST['csrf']) && $_POST['action'] === 'delete') {
    if (Auth::verifyCsrf($_POST['csrf'])) {
        $db->delete('posts', 'id = ?', [(int) $_POST['id']]);
    }
    header('Location: ' . SITE_URL . '/admin/posts.php');
    exit;
}

// Filters
$filterCategory = isset($_GET['category_id']) && $_GET['category_id'] !== '' ? (int) $_GET['category_id'] : null;
$filterStatus   = isset($_GET['status']) && in_array($_GET['status'], ['published', 'draft'], true) ? $_GET['status'] : '';
$search         = isset($_GET['s']) ? trim($_GET['s']) : '';

// Build query
$where  = [];
$params = [];

if ($filterCategory !== null) {
    $where[]  = 'p.category_id = ?';
    $params[] = $filterCategory;
}
if ($filterStatus !== '') {
    $where[]  = 'p.status = ?';
    $params[] = $filterStatus;
}
if ($search !== '') {
    $where[]  = 'p.title LIKE ?';
    $params[] = '%' . $search . '%';
}

$whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$posts = $db->fetchAll(
    "SELECT p.id, p.title, p.slug, p.status, p.published_at, p.created_at,
            c.name AS category_name
     FROM posts p
     LEFT JOIN categories c ON c.id = p.category_id
     {$whereClause}
     ORDER BY p.created_at DESC",
    $params
);

$categories = $db->fetchAll('SELECT id, name FROM categories ORDER BY name ASC');
$csrf       = Auth::generateCsrf();

// Totals
$totalAll       = (int) $db->fetch('SELECT COUNT(*) AS n FROM posts')['n'];
$totalPublished = (int) $db->fetch("SELECT COUNT(*) AS n FROM posts WHERE status = 'published'")['n'];
$totalDraft     = (int) $db->fetch("SELECT COUNT(*) AS n FROM posts WHERE status = 'draft'")['n'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Posts &mdash; SquareStack CMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-100">
<?php $currentPage = 'posts'; ?>
<div class="flex min-h-screen w-full">

    <?php require __DIR__ . '/partials/sidebar.php'; ?>

<!-- Main -->
<div class="flex-1 flex flex-col min-h-screen">

    <!-- Top bar -->
    <header class="bg-white border-b border-gray-200 px-8 py-4 flex items-center justify-between sticky top-0 z-20">
        <div>
            <h1 class="text-xl font-semibold text-gray-800">Posts</h1>
            <p class="text-sm text-gray-500 mt-0.5">Manage your blog posts</p>
        </div>
        <a href="<?= SITE_URL ?>/admin/posts-edit.php"
           class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
            <i class="fa-solid fa-plus"></i> Add New Post
        </a>
    </header>

    <main class="flex-1 px-8 py-8">

        <!-- Stats strip -->
        <div class="flex items-center gap-6 mb-6 text-sm">
            <a href="<?= SITE_URL ?>/admin/posts.php"
               class="<?= ($filterStatus === '' && $filterCategory === null && $search === '') ? 'text-indigo-600 font-semibold' : 'text-gray-500 hover:text-gray-800' ?> transition">
                All <span class="ml-1 bg-gray-100 text-gray-600 rounded-full px-2 py-0.5 text-xs"><?= $totalAll ?></span>
            </a>
            <a href="<?= SITE_URL ?>/admin/posts.php?status=published"
               class="<?= $filterStatus === 'published' ? 'text-indigo-600 font-semibold' : 'text-gray-500 hover:text-gray-800' ?> transition">
                Published <span class="ml-1 bg-green-50 text-green-700 rounded-full px-2 py-0.5 text-xs"><?= $totalPublished ?></span>
            </a>
            <a href="<?= SITE_URL ?>/admin/posts.php?status=draft"
               class="<?= $filterStatus === 'draft' ? 'text-indigo-600 font-semibold' : 'text-gray-500 hover:text-gray-800' ?> transition">
                Drafts <span class="ml-1 bg-yellow-50 text-yellow-700 rounded-full px-2 py-0.5 text-xs"><?= $totalDraft ?></span>
            </a>
        </div>

        <!-- Filters bar -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 px-5 py-4 mb-6">
            <form method="get" action="<?= SITE_URL ?>/admin/posts.php" class="flex flex-wrap items-end gap-4">
                <!-- Search -->
                <div class="flex-1 min-w-[180px]">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Search</label>
                    <div class="relative">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                        <input type="text" name="s" value="<?= htmlspecialchars($search) ?>"
                               placeholder="Search posts&hellip;"
                               class="w-full pl-8 pr-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>
                <!-- Category -->
                <div class="w-48">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Category</label>
                    <select name="category_id"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"
                                <?= $filterCategory === (int) $cat['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <!-- Status -->
                <div class="w-40">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                    <select name="status"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">All Statuses</option>
                        <option value="published" <?= $filterStatus === 'published' ? 'selected' : '' ?>>Published</option>
                        <option value="draft"     <?= $filterStatus === 'draft'     ? 'selected' : '' ?>>Draft</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit"
                            class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                        <i class="fa-solid fa-filter text-xs"></i> Filter
                    </button>
                    <?php if ($filterCategory !== null || $filterStatus !== '' || $search !== ''): ?>
                        <a href="<?= SITE_URL ?>/admin/posts.php"
                           class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg transition">
                            <i class="fa-solid fa-xmark text-xs"></i> Clear
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <?php if (empty($posts)): ?>
                <div class="flex flex-col items-center justify-center py-20 text-gray-400">
                    <i class="fa-solid fa-newspaper text-5xl mb-4 opacity-30"></i>
                    <p class="text-lg font-medium">No posts found</p>
                    <p class="text-sm mt-1">
                        <?php if ($filterCategory !== null || $filterStatus !== '' || $search !== ''): ?>
                            Try adjusting your filters or <a href="<?= SITE_URL ?>/admin/posts.php" class="text-indigo-500 hover:underline">clear them</a>.
                        <?php else: ?>
                            <a href="<?= SITE_URL ?>/admin/posts-edit.php" class="text-indigo-500 hover:underline">Create your first post</a>.
                        <?php endif; ?>
                    </p>
                </div>
            <?php else: ?>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            <th class="px-6 py-3 text-left w-full">Title</th>
                            <th class="px-6 py-3 text-left whitespace-nowrap">Category</th>
                            <th class="px-6 py-3 text-left whitespace-nowrap">Status</th>
                            <th class="px-6 py-3 text-left whitespace-nowrap">Published Date</th>
                            <th class="px-6 py-3 text-right whitespace-nowrap">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($posts as $post): ?>
                            <tr class="hover:bg-gray-50 transition group">
                                <!-- Title -->
                                <td class="px-6 py-4">
                                    <a href="<?= SITE_URL ?>/admin/posts-edit.php?id=<?= $post['id'] ?>"
                                       class="font-medium text-gray-900 hover:text-indigo-600 transition">
                                        <?= htmlspecialchars($post['title']) ?>
                                    </a>
                                    <p class="text-xs text-gray-400 mt-0.5 font-mono">/<?= htmlspecialchars($post['slug']) ?></p>
                                </td>
                                <!-- Category -->
                                <td class="px-6 py-4 text-gray-600 whitespace-nowrap">
                                    <?php if ($post['category_name']): ?>
                                        <a href="<?= SITE_URL ?>/admin/posts.php?category_id=<?= urlencode((string) ($filterCategory ?? '')) ?>"
                                           class="inline-flex items-center gap-1 text-xs bg-indigo-50 text-indigo-700 rounded-full px-2.5 py-0.5 hover:bg-indigo-100 transition">
                                            <i class="fa-solid fa-tag text-[10px]"></i>
                                            <?= htmlspecialchars($post['category_name']) ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-gray-400 text-xs italic">Uncategorised</span>
                                    <?php endif; ?>
                                </td>
                                <!-- Status -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($post['status'] === 'published'): ?>
                                        <span class="inline-flex items-center gap-1.5 text-xs font-medium bg-green-50 text-green-700 rounded-full px-2.5 py-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Published
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1.5 text-xs font-medium bg-yellow-50 text-yellow-700 rounded-full px-2.5 py-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-yellow-400"></span> Draft
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <!-- Published Date -->
                                <td class="px-6 py-4 text-gray-500 whitespace-nowrap text-xs">
                                    <?php if ($post['published_at']): ?>
                                        <span title="<?= htmlspecialchars($post['published_at']) ?>">
                                            <?= date('M j, Y', strtotime($post['published_at'])) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-gray-400 italic">&mdash;</span>
                                    <?php endif; ?>
                                </td>
                                <!-- Actions -->
                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1">
                                        <a href="<?= SITE_URL ?>/admin/posts-edit.php?id=<?= $post['id'] ?>"
                                           title="Edit"
                                           class="p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition">
                                            <i class="fa-solid fa-pen-to-square text-sm"></i>
                                        </a>
                                        <a href="<?= SITE_URL ?>/?p=<?= $post['id'] ?>" target="_blank"
                                           title="View"
                                           class="p-2 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded-lg transition">
                                            <i class="fa-solid fa-arrow-up-right-from-square text-sm"></i>
                                        </a>
                                        <button type="button"
                                                onclick="confirmDelete(<?= $post['id'] ?>, <?= htmlspecialchars(json_encode($post['title'])) ?>)"
                                                title="Delete"
                                                class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition">
                                            <i class="fa-solid fa-trash text-sm"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="px-6 py-3 bg-gray-50 border-t border-gray-200 text-xs text-gray-500">
                    Showing <?= count($posts) ?> of <?= $totalAll ?> post<?= $totalAll !== 1 ? 's' : '' ?>
                </div>
            <?php endif; ?>
        </div>

    </main>
</div>

<!-- Delete confirmation modal -->
<div id="deleteModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 p-6">
        <div class="flex items-start gap-4">
            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                <i class="fa-solid fa-triangle-exclamation text-red-600"></i>
            </div>
            <div>
                <h3 class="text-base font-semibold text-gray-900">Delete post</h3>
                <p class="text-sm text-gray-600 mt-1">
                    Are you sure you want to delete <strong id="deletePostTitle" class="text-gray-800"></strong>?
                    This action cannot be undone.
                </p>
            </div>
        </div>
        <form method="post" action="<?= SITE_URL ?>/admin/posts.php" class="mt-6 flex justify-end gap-3">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
            <input type="hidden" name="id" id="deletePostId" value="">
            <button type="button" onclick="closeDeleteModal()"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
                Cancel
            </button>
            <button type="submit"
                    class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition">
                <i class="fa-solid fa-trash mr-1.5"></i> Delete
            </button>
        </form>
    </div>
</div>

<script>
function confirmDelete(id, title) {
    document.getElementById('deletePostId').value = id;
    document.getElementById('deletePostTitle').textContent = title;
    const modal = document.getElementById('deleteModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}
function closeDeleteModal() {
    const modal = document.getElementById('deleteModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) closeDeleteModal();
});
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeDeleteModal();
});
</script>

</div><!-- end flex wrapper -->
</body>
</html>
