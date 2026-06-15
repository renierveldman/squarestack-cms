<?php
require_once dirname(__DIR__) . '/config.php';
require_once CORE_PATH . '/Database.php';
require_once CORE_PATH . '/Auth.php';
require_once CORE_PATH . '/Media.php';

Auth::require();

$currentUser = Auth::currentUser();
$db          = Database::getInstance();
$id          = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$editUser    = $id ? $db->fetch('SELECT id, name, email, role, avatar FROM users WHERE id = ? LIMIT 1', [$id]) : [];
$isEdit      = !empty($editUser);
$success     = false;
$error       = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::verifyCsrf($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid CSRF token.';
    } else {
        $name  = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role  = in_array($_POST['role'] ?? '', ['admin', 'editor']) ? $_POST['role'] : 'editor';
        $pass  = $_POST['password'] ?? '';
        $avatar = trim($_POST['avatar'] ?? ($editUser['avatar'] ?? ''));

        // Handle avatar file upload
        if (!empty($_FILES['avatar_upload']['name'])) {
            try {
                $result = Media::upload($_FILES['avatar_upload']);
                $avatar = $result['url'];
            } catch (Throwable $e) {
                $error = 'Avatar upload failed: ' . $e->getMessage();
            }
        }

        if (!$error) {
            if ($name === '') { $error = 'Name is required.'; }
            elseif ($email === '') { $error = 'Email is required.'; }
            elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $error = 'Invalid email address.'; }
            elseif (!$isEdit && $pass === '') { $error = 'Password is required for new users.'; }
            elseif ($pass !== '' && strlen($pass) < 8) { $error = 'Password must be at least 8 characters.'; }
        }

        if (!$error) {
            // Check email uniqueness
            $existing = $db->fetch('SELECT id FROM users WHERE email = ? LIMIT 1', [$email]);
            if ($existing && (int)$existing['id'] !== $id) {
                $error = 'That email address is already in use.';
            }
        }

        if (!$error) {
            $data = [
                'name'   => $name,
                'email'  => $email,
                'role'   => $role,
                'avatar' => $avatar ?: null,
            ];
            if ($pass !== '') {
                $data['password'] = password_hash($pass, PASSWORD_DEFAULT);
            }

            if ($isEdit) {
                $db->update('users', $data, 'id = ?', [$id]);
                // Refresh session if editing own profile
                if ((int)$id === (int)$currentUser['id']) {
                    $_SESSION['user']['name']   = $name;
                    $_SESSION['user']['email']  = $email;
                    $_SESSION['user']['role']   = $role;
                    $_SESSION['user']['avatar'] = $avatar ?: null;
                }
            } else {
                $id = $db->insert('users', $data);
            }

            header('Location: ' . SITE_URL . '/admin/users.php?saved=1');
            exit;
        }

        // Re-populate form on error
        $editUser = array_merge($editUser ?: [], [
            'name'   => $name,
            'email'  => $email,
            'role'   => $role,
            'avatar' => $avatar,
        ]);
    }
}

$csrf      = Auth::generateCsrf();
$pageTitle = $isEdit ? 'Edit User: ' . ($editUser['name'] ?? '') : 'Add New User';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle) ?> — SquareStack Admin</title>
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
                <h1 class="text-xl font-semibold text-gray-800"><?= htmlspecialchars($pageTitle) ?></h1>
            </div>
            <a href="<?= SITE_URL ?>/admin/users.php"
               class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-800 transition">
                <i class="fa-solid fa-arrow-left text-xs"></i> Back to Users
            </a>
        </header>

        <main class="flex-1 px-8 py-8">
            <div class="max-w-2xl">

                <?php if ($error): ?>
                <div class="mb-6 flex items-center gap-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl px-5 py-4">
                    <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?>
                </div>
                <?php endif; ?>

                <form method="POST" action="<?= SITE_URL ?>/admin/users-edit.php<?= $isEdit ? '?id=' . $id : '' ?>"
                      enctype="multipart/form-data" class="space-y-6">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">

                    <!-- Avatar -->
                    <div class="bg-white rounded-xl border border-gray-200 p-6">
                        <h3 class="font-semibold text-gray-800 mb-4">Profile Photo</h3>
                        <div class="flex items-center gap-6">
                            <div id="avatarPreviewWrap">
                                <?php $currentAvatar = $editUser['avatar'] ?? ''; ?>
                                <?php if ($currentAvatar): ?>
                                <img id="avatarPreview" src="<?= htmlspecialchars($currentAvatar) ?>" alt=""
                                     class="w-20 h-20 rounded-full object-cover border-2 border-gray-200">
                                <?php else: ?>
                                <div id="avatarInitial" class="w-20 h-20 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 text-2xl font-bold">
                                    <?= strtoupper(substr($editUser['name'] ?? 'U', 0, 1)) ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="flex-1 space-y-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Upload new photo</label>
                                    <input type="file" name="avatar_upload" id="avatarUpload" accept="image/*"
                                           class="block w-full text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition">
                                    <p class="text-xs text-gray-400 mt-1">JPEG, PNG, WebP — will be auto-converted to WebP.</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-gray-400">or</span>
                                    <button type="button" onclick="openMediaPicker()"
                                            class="inline-flex items-center gap-1.5 text-xs text-indigo-600 hover:text-indigo-800 font-medium transition">
                                        <i class="fa-solid fa-folder-open"></i> Choose from Media Library
                                    </button>
                                </div>
                                <input type="hidden" name="avatar" id="avatarUrl" value="<?= htmlspecialchars($currentAvatar) ?>">
                                <?php if ($currentAvatar): ?>
                                <button type="button" onclick="clearAvatar()"
                                        class="text-xs text-red-500 hover:text-red-700 transition">
                                    <i class="fa-solid fa-xmark mr-1"></i> Remove photo
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Details -->
                    <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-5">
                        <h3 class="font-semibold text-gray-800">Account Details</h3>
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name <span class="text-red-500">*</span></label>
                            <input type="text" id="name" name="name" required
                                   value="<?= htmlspecialchars($editUser['name'] ?? '') ?>"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                            <input type="email" id="email" name="email" required
                                   value="<?= htmlspecialchars($editUser['email'] ?? '') ?>"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                            <select id="role" name="role"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="admin"  <?= ($editUser['role'] ?? 'editor') === 'admin'  ? 'selected' : '' ?>>Admin — full access</option>
                                <option value="editor" <?= ($editUser['role'] ?? 'editor') === 'editor' ? 'selected' : '' ?>>Editor — content only</option>
                            </select>
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-5">
                        <div>
                            <h3 class="font-semibold text-gray-800"><?= $isEdit ? 'Change Password' : 'Password' ?></h3>
                            <?php if ($isEdit): ?>
                            <p class="text-xs text-gray-400 mt-0.5">Leave blank to keep the current password.</p>
                            <?php endif; ?>
                        </div>
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                                Password <?php if (!$isEdit): ?><span class="text-red-500">*</span><?php endif; ?>
                            </label>
                            <input type="password" id="password" name="password" autocomplete="new-password"
                                   minlength="8"
                                   placeholder="<?= $isEdit ? 'Leave blank to keep current' : 'Min. 8 characters' ?>"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <a href="<?= SITE_URL ?>/admin/users.php"
                           class="text-sm text-gray-500 hover:text-gray-800 transition">Cancel</a>
                        <button type="submit"
                                class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-6 py-2.5 rounded-lg transition">
                            <i class="fa-solid fa-floppy-disk"></i> <?= $isEdit ? 'Save Changes' : 'Create User' ?>
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</div>

<!-- Media Browser Modal -->
<div id="mediaBrowserModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl mx-4 flex flex-col" style="max-height:85vh;">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
            <h3 class="text-base font-semibold text-gray-900">Select Photo</h3>
            <button type="button" onclick="closeMediaBrowser()" class="text-gray-400 hover:text-gray-700 transition">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-6">
            <div id="mediaBrowserGrid" class="grid grid-cols-4 gap-3"></div>
        </div>
        <div class="px-6 py-4 border-t border-gray-200 flex justify-end">
            <button type="button" onclick="closeMediaBrowser()"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">Cancel</button>
        </div>
    </div>
</div>

<script>
// Avatar file preview
document.getElementById('avatarUpload').addEventListener('change', function () {
    const file = this.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
        setAvatarPreview(e.target.result);
        document.getElementById('avatarUrl').value = '';
    };
    reader.readAsDataURL(file);
});

function setAvatarPreview(src) {
    const wrap = document.getElementById('avatarPreviewWrap');
    wrap.innerHTML = `<img id="avatarPreview" src="${src}" alt="" class="w-20 h-20 rounded-full object-cover border-2 border-gray-200">`;
}

function clearAvatar() {
    document.getElementById('avatarUrl').value = '';
    document.getElementById('avatarUpload').value = '';
    const initial = <?= json_encode(strtoupper(substr($editUser['name'] ?? 'U', 0, 1))) ?>;
    document.getElementById('avatarPreviewWrap').innerHTML =
        `<div class="w-20 h-20 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 text-2xl font-bold">${initial}</div>`;
}

// Media browser
function openMediaPicker() {
    const modal = document.getElementById('mediaBrowserModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    const grid = document.getElementById('mediaBrowserGrid');
    grid.innerHTML = '<div class="col-span-4 text-center py-10 text-gray-400 text-sm"><i class="fa-solid fa-spinner fa-spin text-2xl mb-3"></i><br>Loading&hellip;</div>';
    fetch('<?= SITE_URL ?>/admin/ajax.php?action=get_media&page=1')
        .then(r => r.json())
        .then(data => {
            grid.innerHTML = '';
            const items = (data.items || []).filter(i => (i.mime_type || '').startsWith('image/'));
            if (!items.length) {
                grid.innerHTML = '<div class="col-span-4 text-center py-10 text-gray-400 text-sm">No images uploaded yet.</div>';
                return;
            }
            items.forEach(item => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'group relative rounded-lg overflow-hidden border-2 border-transparent hover:border-indigo-500 transition aspect-square bg-gray-100';
                btn.innerHTML = `<img src="${escHtml(item.url)}" alt="" class="w-full h-full object-cover">`;
                btn.addEventListener('click', () => {
                    document.getElementById('avatarUrl').value = item.url;
                    document.getElementById('avatarUpload').value = '';
                    setAvatarPreview(item.url);
                    closeMediaBrowser();
                });
                grid.appendChild(btn);
            });
        })
        .catch(() => {
            grid.innerHTML = '<div class="col-span-4 text-center py-10 text-red-400 text-sm">Failed to load media.</div>';
        });
}

function closeMediaBrowser() {
    const modal = document.getElementById('mediaBrowserModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

document.getElementById('mediaBrowserModal').addEventListener('click', e => {
    if (e.target === document.getElementById('mediaBrowserModal')) closeMediaBrowser();
});
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeMediaBrowser(); });
</script>

</body>
</html>
