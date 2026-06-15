<?php
require_once dirname(__DIR__) . '/config.php';
require_once CORE_PATH . '/Database.php';
require_once CORE_PATH . '/Auth.php';

Auth::require();

$currentUser = Auth::currentUser();
$db          = Database::getInstance();
$csrf        = Auth::generateCsrf();

$users = $db->fetchAll('SELECT id, name, email, role, avatar, created_at FROM users ORDER BY created_at ASC');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Users — SquareStack Admin</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body class="bg-gray-100">
<?php $currentPage = 'users'; ?>
<div class="flex min-h-screen w-full">

    <?php require __DIR__ . '/partials/sidebar.php'; ?>

    <!-- Main -->
    <div class="flex-1 flex flex-col min-h-screen">
        <header class="bg-white border-b border-gray-200 px-8 py-4 flex items-center justify-between sticky top-0 z-20">
            <div>
                <h1 class="text-xl font-semibold text-gray-800">Users</h1>
                <p class="text-sm text-gray-500 mt-0.5"><?= count($users) ?> user<?= count($users) !== 1 ? 's' : '' ?> total</p>
            </div>
            <a href="<?= SITE_URL ?>/admin/users-edit.php"
               class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                <i class="fa-solid fa-plus"></i> Add New User
            </a>
        </header>

        <main class="flex-1 px-8 py-8">

            <?php if (isset($_GET['saved'])): ?>
            <div class="mb-6 flex items-center gap-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-xl px-5 py-4">
                <i class="fa-solid fa-circle-check"></i> User saved successfully.
            </div>
            <?php endif; ?>

            <?php if (isset($_GET['deleted'])): ?>
            <div class="mb-6 flex items-center gap-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-xl px-5 py-4">
                <i class="fa-solid fa-circle-check"></i> User deleted successfully.
            </div>
            <?php endif; ?>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <?php if (empty($users)): ?>
                <div class="text-center py-16 text-gray-400">
                    <i class="fa-solid fa-users text-4xl mb-3 opacity-30"></i>
                    <p class="text-sm">No users yet.</p>
                </div>
                <?php else: ?>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">User</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Role</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Joined</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        <?php foreach ($users as $u): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <?php if (!empty($u['avatar'])): ?>
                                    <img src="<?= htmlspecialchars($u['avatar']) ?>" alt=""
                                         class="w-9 h-9 rounded-full object-cover flex-shrink-0 border border-gray-200">
                                    <?php else: ?>
                                    <div class="w-9 h-9 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 text-sm font-bold flex-shrink-0">
                                        <?= strtoupper(substr($u['name'] ?? 'U', 0, 1)) ?>
                                    </div>
                                    <?php endif; ?>
                                    <span class="font-medium text-gray-900 text-sm">
                                        <?= htmlspecialchars($u['name'] ?? '') ?>
                                        <?php if ((int)$u['id'] === (int)$currentUser['id']): ?>
                                        <span class="ml-1 text-xs text-gray-400">(you)</span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500"><?= htmlspecialchars($u['email'] ?? '') ?></td>
                            <td class="px-6 py-4">
                                <?php if ($u['role'] === 'admin'): ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700">Admin</span>
                                <?php else: ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">Editor</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                <?= $u['created_at'] ? date('M j, Y', strtotime($u['created_at'])) : '&mdash;' ?>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-3">
                                    <a href="<?= SITE_URL ?>/admin/users-edit.php?id=<?= (int)$u['id'] ?>"
                                       class="text-indigo-600 hover:text-indigo-800 text-sm font-medium transition">
                                        <i class="fa-solid fa-pen-to-square"></i> <span class="ml-1">Edit</span>
                                    </a>
                                    <?php if ((int)$u['id'] !== (int)$currentUser['id']): ?>
                                    <button type="button"
                                            class="text-red-500 hover:text-red-700 text-sm font-medium transition js-delete-user"
                                            data-id="<?= (int)$u['id'] ?>"
                                            data-name="<?= htmlspecialchars($u['name'] ?? '', ENT_QUOTES) ?>"
                                            data-csrf="<?= htmlspecialchars($csrf) ?>">
                                        <i class="fa-solid fa-trash-can"></i> <span class="ml-1">Delete</span>
                                    </button>
                                    <?php endif; ?>
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
</div>

<!-- Delete modal -->
<div id="deleteModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4 p-6">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                <i class="fa-solid fa-trash-can text-red-500"></i>
            </div>
            <div>
                <h3 class="text-base font-semibold text-gray-900">Delete User</h3>
                <p class="text-sm text-gray-500">This action cannot be undone.</p>
            </div>
        </div>
        <p class="text-sm text-gray-700 mb-6">Are you sure you want to delete <strong id="deleteUserName" class="text-gray-900"></strong>?</p>
        <div class="flex justify-end gap-3">
            <button type="button" id="cancelDelete"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">Cancel</button>
            <input type="hidden" id="deleteUserId">
            <input type="hidden" id="deleteCsrf">
            <button type="button" id="confirmDelete"
                    class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition">
                <i class="fa-solid fa-trash-can mr-1"></i> Delete
            </button>
        </div>
    </div>
</div>

<script>
(function () {
    const modal      = document.getElementById('deleteModal');
    const nameEl     = document.getElementById('deleteUserName');
    const idInput    = document.getElementById('deleteUserId');
    const csrfInput  = document.getElementById('deleteCsrf');
    const cancelBtn  = document.getElementById('cancelDelete');
    const confirmBtn = document.getElementById('confirmDelete');

    document.querySelectorAll('.js-delete-user').forEach(btn => {
        btn.addEventListener('click', () => {
            nameEl.textContent = btn.dataset.name;
            idInput.value      = btn.dataset.id;
            csrfInput.value    = btn.dataset.csrf;
            modal.classList.remove('hidden');
        });
    });

    cancelBtn.addEventListener('click', () => modal.classList.add('hidden'));
    modal.addEventListener('click', e => { if (e.target === modal) modal.classList.add('hidden'); });

    confirmBtn.addEventListener('click', () => {
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i> Deleting…';
        fetch('<?= SITE_URL ?>/admin/ajax.php?action=delete_user', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'id=' + encodeURIComponent(idInput.value) + '&csrf_token=' + encodeURIComponent(csrfInput.value)
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                window.location.href = '<?= SITE_URL ?>/admin/users.php?deleted=1';
            } else {
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = '<i class="fa-solid fa-trash-can mr-1"></i> Delete';
                alert(data.error || 'Failed to delete user.');
            }
        })
        .catch(() => {
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = '<i class="fa-solid fa-trash-can mr-1"></i> Delete';
            alert('An error occurred.');
        });
    });
})();
</script>

</body>
</html>
