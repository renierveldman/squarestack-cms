<?php
require_once dirname(__DIR__) . '/config.php';
require_once CORE_PATH . '/Database.php';
require_once CORE_PATH . '/Cache.php';
require_once CORE_PATH . '/Auth.php';
require_once CORE_PATH . '/CMS.php';
require_once CORE_PATH . '/Media.php';
require_once CORE_PATH . '/helpers.php';

Auth::require();

// Load theme functions so field groups are registered
$themeFunctions = THEME_PATH . '/functions.php';
if (file_exists($themeFunctions)) require_once $themeFunctions;

$fieldGroups = CMS::getFieldGroups('option');
$user        = Auth::currentUser();
$success     = false;
$error       = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::verifyCsrf($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid CSRF token.';
    } else {
        foreach ($fieldGroups as $group) {
            foreach ($group['fields'] as $field) {
                $name = $field['name'];
                if ($field['type'] === 'toggle') {
                    $value = isset($_POST['cf_' . $name]) ? '1' : '';
                } else {
                    $value = $_POST['cf_' . $name] ?? '';
                }
                CMS::saveMeta('option', 0, $name, $value);
            }
        }
        Cache::flush();
        $success = true;
    }
}

// Load current option values
$meta = CMS::getAllMeta('option', 0);
$csrf = Auth::generateCsrf();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Theme Options — SquareStack Admin</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body class="bg-gray-100">
<?php $currentPage = 'theme_options'; ?>
<div class="flex min-h-screen w-full">

    <?php require __DIR__ . '/partials/sidebar.php'; ?>

    <!-- Main -->
    <div class="flex-1 flex flex-col min-h-screen">
        <header class="bg-white border-b border-gray-200 px-8 py-4 flex items-center justify-between sticky top-0 z-20">
            <div>
                <h1 class="text-xl font-semibold text-gray-800">Theme Options</h1>
                <p class="text-sm text-gray-500 mt-0.5">Global settings available throughout your theme</p>
            </div>
        </header>

        <main class="flex-1 px-8 py-8 max-w-3xl">

            <?php if ($error): ?>
            <div class="mb-6 flex items-center gap-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl px-5 py-4">
                <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <?php if ($success): ?>
            <div class="mb-6 flex items-center gap-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-xl px-5 py-4">
                <i class="fa-solid fa-circle-check"></i> Theme options saved successfully.
            </div>
            <?php endif; ?>

            <?php if (empty($fieldGroups)): ?>
            <div class="bg-white rounded-xl border border-gray-200 p-12 text-center text-gray-400">
                <i class="fa-solid fa-palette text-4xl mb-4 opacity-30"></i>
                <p class="text-sm font-medium text-gray-500 mb-1">No theme options registered</p>
                <p class="text-xs text-gray-400">Add option field groups to your theme's <code class="bg-gray-100 px-1 rounded">functions.php</code> using <code class="bg-gray-100 px-1 rounded">'type' => 'option'</code> as the location.</p>
            </div>
            <?php else: ?>
            <form method="POST" action="<?= SITE_URL ?>/admin/theme-options.php">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">

                <div class="space-y-6">
                    <?php foreach ($fieldGroups as $group): ?>
                    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-2">
                            <i class="fa-solid fa-sliders text-indigo-500 text-sm"></i>
                            <h3 class="font-semibold text-gray-800"><?= htmlspecialchars($group['title']) ?></h3>
                        </div>
                        <div class="px-6 py-5 space-y-5">
                        <?php foreach ($group['fields'] as $field): ?>
                            <?php
                            $name = $field['name'];
                            $val  = $meta[$name] ?? ($field['default'] ?? '');
                            ?>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    <?= htmlspecialchars($field['label']) ?>
                                    <?php if (!empty($field['description'])): ?>
                                    <span class="text-gray-400 font-normal ml-1"><?= htmlspecialchars($field['description']) ?></span>
                                    <?php endif; ?>
                                </label>

                                <?php if ($field['type'] === 'text' || $field['type'] === 'email' || $field['type'] === 'url' || $field['type'] === 'tel'): ?>
                                    <input type="<?= htmlspecialchars($field['type']) ?>"
                                           name="cf_<?= htmlspecialchars($name) ?>"
                                           value="<?= htmlspecialchars($val) ?>"
                                           placeholder="<?= htmlspecialchars($field['placeholder'] ?? '') ?>"
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">

                                <?php elseif ($field['type'] === 'textarea'): ?>
                                    <textarea name="cf_<?= htmlspecialchars($name) ?>"
                                              rows="<?= (int)($field['rows'] ?? 3) ?>"
                                              placeholder="<?= htmlspecialchars($field['placeholder'] ?? '') ?>"
                                              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-y"><?= htmlspecialchars($val) ?></textarea>

                                <?php elseif ($field['type'] === 'image'): ?>
                                    <div class="flex gap-2">
                                        <input type="text"
                                               name="cf_<?= htmlspecialchars($name) ?>"
                                               id="field_<?= htmlspecialchars($name) ?>"
                                               value="<?= htmlspecialchars($val) ?>"
                                               placeholder="https://..."
                                               class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                        <button type="button"
                                                onclick="openMediaPicker('field_<?= htmlspecialchars($name) ?>')"
                                                class="px-3 py-2 bg-gray-100 border border-gray-300 rounded-lg text-sm hover:bg-gray-200 transition whitespace-nowrap">
                                            <i class="fa-solid fa-folder-open mr-1"></i> Browse
                                        </button>
                                    </div>
                                    <?php if ($val): ?>
                                    <img src="<?= htmlspecialchars($val) ?>" alt=""
                                         class="mt-2 h-20 rounded-lg object-cover border border-gray-200"
                                         onerror="this.style.display='none'">
                                    <?php endif; ?>

                                <?php elseif ($field['type'] === 'toggle'): ?>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox"
                                               name="cf_<?= htmlspecialchars($name) ?>"
                                               value="1"
                                               <?= $val ? 'checked' : '' ?>
                                               class="w-4 h-4 text-indigo-600 rounded border-gray-300">
                                        <span class="text-sm text-gray-600"><?= htmlspecialchars($field['toggle_label'] ?? 'Enable') ?></span>
                                    </label>

                                <?php elseif ($field['type'] === 'select'): ?>
                                    <select name="cf_<?= htmlspecialchars($name) ?>"
                                            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                        <?php foreach ($field['options'] ?? [] as $optVal => $optLabel): ?>
                                        <option value="<?= htmlspecialchars($optVal) ?>" <?= $val === $optVal ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($optLabel) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>

                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit"
                            class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-6 py-2.5 rounded-lg transition">
                        <i class="fa-solid fa-floppy-disk"></i> Save Theme Options
                    </button>
                </div>
            </form>
            <?php endif; ?>

        </main>
    </div>
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
            <div id="mediaBrowserGrid" class="grid grid-cols-4 gap-3"></div>
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
var _mediaTarget = null;

function openMediaPicker(inputId) {
    _mediaTarget = inputId;
    const modal = document.getElementById('mediaBrowserModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    const grid = document.getElementById('mediaBrowserGrid');
    grid.innerHTML = '<div class="col-span-4 text-center py-10 text-gray-400 text-sm"><i class="fa-solid fa-spinner fa-spin text-2xl mb-3"></i><br>Loading&hellip;</div>';

    fetch('<?= SITE_URL ?>/admin/ajax.php?action=get_media&page=1')
        .then(r => r.json())
        .then(data => {
            grid.innerHTML = '';
            const items = data.items || [];
            if (!items.length) {
                grid.innerHTML = '<div class="col-span-4 text-center py-10 text-gray-400 text-sm">No media uploaded yet.</div>';
                return;
            }
            items.forEach(item => {
                if (!(item.mime_type || '').startsWith('image/')) return;
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'group relative rounded-lg overflow-hidden border-2 border-transparent hover:border-indigo-500 transition aspect-square bg-gray-100';
                btn.innerHTML = `<img src="${escHtml(item.url)}" alt="" class="w-full h-full object-cover">`;
                btn.addEventListener('click', () => {
                    const input = document.getElementById(_mediaTarget);
                    if (input) {
                        input.value = item.url;
                        // Update adjacent image preview if present
                        const preview = input.parentElement.parentElement.querySelector('img');
                        if (preview) { preview.src = item.url; preview.style.display = ''; }
                    }
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
    _mediaTarget = null;
}

function escHtml(str) {
    return String(str).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

document.getElementById('mediaBrowserModal').addEventListener('click', e => {
    if (e.target === document.getElementById('mediaBrowserModal')) closeMediaBrowser();
});
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeMediaBrowser(); });
</script>

</body>
</html>
