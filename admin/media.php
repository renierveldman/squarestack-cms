<?php
require_once dirname(__DIR__) . '/config.php';
require_once CORE_PATH . '/Database.php';
require_once CORE_PATH . '/Cache.php';
require_once CORE_PATH . '/Settings.php';
require_once CORE_PATH . '/Slug.php';
require_once CORE_PATH . '/CMS.php';
require_once CORE_PATH . '/Auth.php';
require_once CORE_PATH . '/Media.php';

Auth::require();

$csrf     = Auth::generateCsrf();
$user     = Auth::currentUser();
$page     = max(1, (int)($_GET['page'] ?? 1));
$library  = Media::getLibrary($page, 24);
$items    = $library['items'];
$total    = $library['total'];
$pages    = $library['pages'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Media — SquareStack Admin</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="bg-gray-100 text-gray-900">
<div class="flex min-h-screen">

    <!-- Sidebar -->
    <aside class="w-64 bg-slate-900 text-white flex flex-col flex-shrink-0">
        <div class="p-6 border-b border-slate-700">
            <a href="<?= SITE_URL ?>/admin/" class="text-xl font-bold text-white"><?= htmlspecialchars(Settings::get('site_name', 'SquareStack')) ?></a>
            <p class="text-slate-400 text-xs mt-1">Admin Panel</p>
        </div>
        <nav class="flex-1 p-4 space-y-1">
            <a href="<?= SITE_URL ?>/admin/" class="flex items-center gap-3 px-3 py-2 rounded-lg text-slate-300 hover:bg-slate-800 hover:text-white text-sm"><i class="fa fa-gauge w-4"></i> Dashboard</a>
            <a href="<?= SITE_URL ?>/admin/pages.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-slate-300 hover:bg-slate-800 hover:text-white text-sm"><i class="fa fa-file w-4"></i> Pages</a>
            <a href="<?= SITE_URL ?>/admin/posts.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-slate-300 hover:bg-slate-800 hover:text-white text-sm"><i class="fa fa-newspaper w-4"></i> Posts</a>
            <a href="<?= SITE_URL ?>/admin/menus.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-slate-300 hover:bg-slate-800 hover:text-white text-sm"><i class="fa fa-bars w-4"></i> Menus</a>
            <a href="<?= SITE_URL ?>/admin/media.php" class="flex items-center gap-3 px-3 py-2 rounded-lg bg-slate-700 text-white text-sm font-medium"><i class="fa fa-images w-4"></i> Media</a>
            <a href="<?= SITE_URL ?>/admin/settings.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-slate-300 hover:bg-slate-800 hover:text-white text-sm"><i class="fa fa-gear w-4"></i> Settings</a>
        </nav>
        <div class="p-4 border-t border-slate-700">
            <p class="text-slate-400 text-xs mb-2"><?= htmlspecialchars($user['name'] ?? '') ?></p>
            <a href="<?= SITE_URL ?>/admin/logout.php" class="text-slate-400 hover:text-white text-xs"><i class="fa fa-right-from-bracket mr-1"></i>Logout</a>
        </div>
    </aside>

    <!-- Main -->
    <div class="flex-1 flex flex-col">
        <header class="bg-white border-b border-gray-200 px-8 py-4">
            <h1 class="text-xl font-semibold">Media Library</h1>
        </header>

        <div class="p-8 space-y-6">

            <!-- Upload Zone -->
            <div id="upload-zone"
                class="border-2 border-dashed border-gray-300 rounded-xl p-12 text-center bg-white cursor-pointer hover:border-indigo-400 hover:bg-indigo-50 transition-colors"
                onclick="document.getElementById('file-input').click()"
                ondragover="event.preventDefault(); this.classList.add('border-indigo-500','bg-indigo-50')"
                ondragleave="this.classList.remove('border-indigo-500','bg-indigo-50')"
                ondrop="handleDrop(event)">
                <i class="fa fa-cloud-arrow-up text-4xl text-gray-400 mb-3"></i>
                <p class="text-gray-600 font-medium">Drop files here or click to upload</p>
                <p class="text-gray-400 text-sm mt-1">JPEG, PNG, GIF, WebP, PDF — max 10MB</p>
                <input type="file" id="file-input" multiple accept="image/*,.pdf" class="hidden" onchange="uploadFiles(this.files)">
            </div>

            <!-- Upload Progress -->
            <div id="upload-progress" class="hidden space-y-2"></div>

            <!-- Stats bar -->
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-500"><?= $total ?> file<?= $total !== 1 ? 's' : '' ?></p>
                <?php if ($pages > 1): ?>
                <div class="flex gap-2">
                    <?php for ($i = 1; $i <= $pages; $i++): ?>
                    <a href="?page=<?= $i ?>" class="px-3 py-1 rounded <?= $i === $page ? 'bg-indigo-600 text-white' : 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50' ?> text-sm"><?= $i ?></a>
                    <?php endfor; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Grid -->
            <div id="media-grid" class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-4">
                <?php foreach ($items as $item): ?>
                <div class="group cursor-pointer" onclick="openModal(<?= htmlspecialchars(json_encode($item)) ?>)">
                    <div class="aspect-square bg-gray-100 rounded-lg overflow-hidden border border-gray-200 group-hover:border-indigo-400 transition-colors">
                        <?php if (str_starts_with($item['mime_type'] ?? '', 'image/')): ?>
                        <img src="<?= htmlspecialchars($item['url'] ?? '') ?>" alt="<?= htmlspecialchars($item['alt_text'] ?? '') ?>" class="w-full h-full object-cover" loading="lazy">
                        <?php else: ?>
                        <div class="w-full h-full flex items-center justify-center text-gray-400">
                            <i class="fa fa-file-pdf text-3xl text-red-400"></i>
                        </div>
                        <?php endif; ?>
                    </div>
                    <p class="text-xs text-gray-500 mt-1 truncate"><?= htmlspecialchars($item['original_name'] ?? '') ?></p>
                </div>
                <?php endforeach; ?>
                <?php if (empty($items)): ?>
                <div class="col-span-6 text-center py-16 text-gray-400">
                    <i class="fa fa-images text-4xl mb-3"></i>
                    <p>No media uploaded yet.</p>
                </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

<!-- Media Detail Modal -->
<div id="media-modal" class="fixed inset-0 bg-black/70 z-50 hidden flex items-center justify-center p-4" onclick="if(event.target===this)closeModal()">
    <div class="bg-white rounded-xl w-full max-w-2xl shadow-2xl">
        <div class="flex items-center justify-between p-4 border-b border-gray-200">
            <h3 class="font-semibold" id="modal-filename">File details</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600"><i class="fa fa-xmark text-xl"></i></button>
        </div>
        <div class="flex gap-0">
            <!-- Preview -->
            <div class="w-64 bg-gray-100 flex items-center justify-center p-4 flex-shrink-0 rounded-bl-xl">
                <img id="modal-preview" src="" alt="" class="max-w-full max-h-56 object-contain rounded">
            </div>
            <!-- Details -->
            <div class="flex-1 p-6 space-y-4">
                <div>
                    <p class="text-xs text-gray-500 mb-1">URL</p>
                    <div class="flex gap-2">
                        <input type="text" id="modal-url" readonly class="flex-1 border border-gray-200 rounded px-2 py-1.5 text-xs bg-gray-50 font-mono" onclick="this.select()">
                        <button onclick="copyUrl()" class="px-2 py-1 bg-gray-100 border border-gray-200 rounded text-xs hover:bg-gray-200"><i class="fa fa-copy"></i></button>
                    </div>
                </div>
                <div>
                    <p class="text-xs text-gray-500 mb-1">Alt Text</p>
                    <input type="text" id="modal-alt" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="text-xs text-gray-500 space-y-0.5">
                    <p id="modal-size"></p>
                    <p id="modal-dimensions"></p>
                    <p id="modal-date"></p>
                </div>
                <div class="flex gap-3 pt-2 border-t border-gray-100">
                    <button onclick="deleteMedia()" class="text-red-600 hover:text-red-800 text-sm"><i class="fa fa-trash mr-1"></i>Delete</button>
                    <span class="text-gray-300">|</span>
                    <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700 text-sm">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const SITE_URL = '<?= SITE_URL ?>';
const CSRF = '<?= htmlspecialchars($csrf) ?>';
let currentMediaId = null;

function handleDrop(e) {
    e.preventDefault();
    document.getElementById('upload-zone').classList.remove('border-indigo-500','bg-indigo-50');
    uploadFiles(e.dataTransfer.files);
}

function uploadFiles(files) {
    Array.from(files).forEach(file => {
        const bar = document.createElement('div');
        bar.className = 'flex items-center gap-3 bg-white rounded-lg p-3 border border-gray-200';
        bar.innerHTML = `<i class="fa fa-file text-gray-400"></i><span class="flex-1 text-sm text-gray-600 truncate">${file.name}</span><span class="text-xs text-gray-400" id="status-${Date.now()}">Uploading…</span>`;
        const prog = document.getElementById('upload-progress');
        prog.classList.remove('hidden');
        prog.appendChild(bar);

        const fd = new FormData();
        fd.append('file', file);
        fd.append('csrf_token', CSRF);

        fetch(SITE_URL + '/admin/ajax.php?action=upload_media', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    bar.querySelector('span:last-child').textContent = '✓ Done';
                    bar.querySelector('span:last-child').className = 'text-xs text-green-600';
                    addToGrid(data);
                } else {
                    bar.querySelector('span:last-child').textContent = '✗ ' + (data.error || 'Failed');
                    bar.querySelector('span:last-child').className = 'text-xs text-red-600';
                }
            })
            .catch(() => { bar.querySelector('span:last-child').textContent = '✗ Error'; });
    });
}

function addToGrid(item) {
    const grid = document.getElementById('media-grid');
    const empty = grid.querySelector('.col-span-6');
    if (empty) empty.remove();

    const div = document.createElement('div');
    div.className = 'group cursor-pointer';
    div.onclick = () => openModal(item);
    div.innerHTML = `
        <div class="aspect-square bg-gray-100 rounded-lg overflow-hidden border border-gray-200 group-hover:border-indigo-400 transition-colors">
            <img src="${item.url}" alt="${item.alt_text||''}" class="w-full h-full object-cover" loading="lazy">
        </div>
        <p class="text-xs text-gray-500 mt-1 truncate">${item.original_name||item.filename}</p>`;
    grid.prepend(div);
}

function openModal(item) {
    currentMediaId = item.id;
    document.getElementById('modal-filename').textContent = item.original_name || item.filename;
    document.getElementById('modal-url').value = item.url;
    document.getElementById('modal-alt').value = item.alt_text || '';
    document.getElementById('modal-preview').src = item.url;
    document.getElementById('modal-size').textContent = 'Size: ' + formatSize(item.file_size);
    document.getElementById('modal-dimensions').textContent = item.width ? `Dimensions: ${item.width}×${item.height}` : '';
    document.getElementById('modal-date').textContent = item.created_at ? 'Uploaded: ' + item.created_at : '';
    document.getElementById('media-modal').classList.remove('hidden');
}

function closeModal() { document.getElementById('media-modal').classList.add('hidden'); currentMediaId = null; }

function copyUrl() {
    const url = document.getElementById('modal-url').value;
    navigator.clipboard.writeText(url).then(() => {
        const btn = document.querySelector('[onclick="copyUrl()"]');
        btn.innerHTML = '<i class="fa fa-check text-green-600"></i>';
        setTimeout(() => btn.innerHTML = '<i class="fa fa-copy"></i>', 2000);
    });
}

function deleteMedia() {
    if (!currentMediaId || !confirm('Delete this file? This cannot be undone.')) return;
    fetch(SITE_URL + '/admin/ajax.php?action=delete_media', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'id=' + currentMediaId + '&csrf_token=' + CSRF
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            closeModal();
            location.reload();
        }
    });
}

function formatSize(bytes) {
    if (!bytes) return '—';
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024*1024) return (bytes/1024).toFixed(1) + ' KB';
    return (bytes/(1024*1024)).toFixed(1) + ' MB';
}
</script>
</body>
</html>
