<?php
require_once dirname(__DIR__) . '/config.php';
require_once CORE_PATH . '/Database.php';
require_once CORE_PATH . '/Auth.php';
require_once CORE_PATH . '/Settings.php';
require_once CORE_PATH . '/CMS.php';
require_once CORE_PATH . '/Cache.php';

Auth::require();

$db       = Database::getInstance();
$siteName = Settings::get('site_name', 'SquareStack CMS');
$user     = Auth::currentUser();

$locations = [
    'primary' => 'Primary Navigation',
    'footer'  => 'Footer Navigation',
];

// Load menus and their flat items
$menus = [];
foreach ($locations as $loc => $locLabel) {
    $menu = $db->fetch('SELECT * FROM `menus` WHERE `location` = ? LIMIT 1', [$loc]);
    if (!$menu) {
        $menuId = $db->insert('menus', ['name' => $locLabel, 'location' => $loc]);
        $menu = ['id' => $menuId, 'name' => $locLabel, 'location' => $loc];
    }
    $items = $db->fetchAll(
        'SELECT * FROM `menu_items` WHERE `menu_id` = ? ORDER BY `sort_order` ASC, `id` ASC',
        [(int) $menu['id']]
    );
    $menu['items'] = $items;
    $menus[$loc]   = $menu;
}

// Published pages for "Add from Pages"
$publishedPages = CMS::getPages(['status' => 'published', 'order_by' => 'title ASC']);

$csrfToken = Auth::generateCsrf();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menus &mdash; <?php echo htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8'); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        .drag-handle { cursor: grab; }
        .drag-handle:active { cursor: grabbing; }
        .menu-item.dragging { opacity: 0.4; }
        .menu-item.drag-over { border-top: 2px solid #6366f1; }
    </style>
</head>
<body class="bg-gray-100">

<div class="flex min-h-screen w-full">

    <?php $currentPage = 'menus'; ?>
    <?php require __DIR__ . '/partials/sidebar.php'; ?>

    <!-- Main content -->
    <main class="flex-1 p-8 overflow-y-auto">

        <div class="mb-8">
            <h1 class="text-2xl font-bold text-slate-800">Menus</h1>
            <p class="text-slate-500 text-sm mt-1">Manage navigation menus for your site.</p>
        </div>

        <!-- Flash messages -->
        <div id="flash" class="hidden mb-6 rounded-lg px-4 py-3 text-sm font-medium"></div>

        <?php foreach ($locations as $loc => $locLabel): ?>
        <?php $menu = $menus[$loc]; ?>
        <section class="bg-white rounded-2xl shadow-sm border border-slate-200 mb-10 overflow-hidden">

            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-slate-800"><?php echo htmlspecialchars($locLabel, ENT_QUOTES, 'UTF-8'); ?></h2>
                    <p class="text-xs text-slate-400 mt-0.5">Location: <code class="bg-slate-100 px-1 rounded"><?php echo htmlspecialchars($loc, ENT_QUOTES, 'UTF-8'); ?></code></p>
                </div>
                <button type="button"
                        onclick="saveMenu('<?php echo $loc; ?>')"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 active:bg-indigo-700 transition">
                    Save Menu
                </button>
            </div>

            <div class="p-6 grid grid-cols-1 lg:grid-cols-3 gap-8">

                <!-- Current items list -->
                <div class="lg:col-span-2">
                    <h3 class="text-sm font-semibold text-slate-700 mb-3">Current Items</h3>
                    <ul id="menu-list-<?php echo $loc; ?>"
                        class="space-y-2 min-h-[60px]"
                        data-location="<?php echo $loc; ?>">
                        <?php if (empty($menu['items'])): ?>
                        <li class="text-sm text-slate-400 italic py-2 empty-placeholder">No items yet. Add one below.</li>
                        <?php else: ?>
                        <?php foreach ($menu['items'] as $item): ?>
                        <li class="menu-item flex items-center gap-3 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5"
                            draggable="true"
                            data-id="<?php echo (int) $item['id']; ?>">
                            <span class="drag-handle text-slate-400 select-none" title="Drag to reorder">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/>
                                </svg>
                            </span>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-slate-800 truncate item-label">
                                    <?php echo htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'); ?>
                                </p>
                                <p class="text-xs text-slate-400 truncate item-url">
                                    <?php echo htmlspecialchars($item['url'], ENT_QUOTES, 'UTF-8'); ?>
                                </p>
                            </div>
                            <span class="text-xs text-slate-400 shrink-0 item-target">
                                <?php echo $item['target'] === '_blank' ? 'new tab' : 'same tab'; ?>
                            </span>
                            <button type="button"
                                    onclick="deleteItem(this)"
                                    class="shrink-0 rounded p-1 text-slate-400 hover:text-red-500 hover:bg-red-50 transition"
                                    title="Remove item">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                            <!-- Hidden data -->
                            <span class="hidden item-data"
                                  data-label="<?php echo htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'); ?>"
                                  data-url="<?php echo htmlspecialchars($item['url'], ENT_QUOTES, 'UTF-8'); ?>"
                                  data-target="<?php echo htmlspecialchars($item['target'], ENT_QUOTES, 'UTF-8'); ?>"></span>
                        </li>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- Right column: add form + add from pages -->
                <div class="space-y-6">

                    <!-- Add new item form -->
                    <div>
                        <h3 class="text-sm font-semibold text-slate-700 mb-3">Add New Item</h3>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1">Label</label>
                                <input type="text"
                                       id="add-label-<?php echo $loc; ?>"
                                       placeholder="e.g. Home"
                                       class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 transition">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1">URL</label>
                                <input type="text"
                                       id="add-url-<?php echo $loc; ?>"
                                       placeholder="e.g. /about"
                                       class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 transition">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1">Open in</label>
                                <select id="add-target-<?php echo $loc; ?>"
                                        class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 transition">
                                    <option value="_self">Same tab</option>
                                    <option value="_blank">New tab</option>
                                </select>
                            </div>
                            <button type="button"
                                    onclick="addItem('<?php echo $loc; ?>')"
                                    class="w-full rounded-lg border border-indigo-300 bg-indigo-50 px-4 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-100 active:bg-indigo-200 transition">
                                Add Item
                            </button>
                        </div>
                    </div>

                    <!-- Add from Pages -->
                    <?php if (!empty($publishedPages)): ?>
                    <div>
                        <h3 class="text-sm font-semibold text-slate-700 mb-3">Add from Pages</h3>
                        <div class="flex flex-wrap gap-2">
                            <?php
                            $basePath = rtrim(parse_url(SITE_URL, PHP_URL_PATH) ?? '', '/');
                            ?>
                            <?php foreach ($publishedPages as $page): ?>
                            <?php
                                $pageSlug = $page['slug'] ?? '';
                                $pageUrl  = ($pageSlug === '' || $pageSlug === 'home')
                                    ? $basePath . '/'
                                    : $basePath . '/' . $pageSlug . '/';
                                $pageTitle = $page['title'] ?? $page['slug'];
                            ?>
                            <button type="button"
                                    onclick="fillFromPage('<?php echo $loc; ?>', <?php echo htmlspecialchars(json_encode($pageTitle), ENT_QUOTES, 'UTF-8'); ?>, <?php echo htmlspecialchars(json_encode($pageUrl), ENT_QUOTES, 'UTF-8'); ?>)"
                                    class="rounded-full border border-slate-300 bg-white px-3 py-1 text-xs font-medium text-slate-700 hover:border-indigo-400 hover:text-indigo-700 hover:bg-indigo-50 transition">
                                <?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?>
                            </button>
                            <?php endforeach; ?>
                        </div>
                        <p class="text-xs text-slate-400 mt-2">Click a page to add it to the menu.</p>
                    </div>
                    <?php endif; ?>

                </div>
            </div>
        </section>
        <?php endforeach; ?>

    </main>
</div>

<script>
const CSRF_TOKEN = <?php echo json_encode($csrfToken); ?>;
const SITE_URL   = <?php echo json_encode(SITE_URL); ?>;

// -----------------------------------------------------------------------
// Drag-and-drop reordering
// -----------------------------------------------------------------------
document.querySelectorAll('[id^="menu-list-"]').forEach(initSortable);

function initSortable(list) {
    let dragged = null;

    list.addEventListener('dragstart', e => {
        const item = e.target.closest('.menu-item');
        if (!item) return;
        dragged = item;
        setTimeout(() => item.classList.add('dragging'), 0);
    });

    list.addEventListener('dragend', e => {
        const item = e.target.closest('.menu-item');
        if (!item) return;
        item.classList.remove('dragging');
        list.querySelectorAll('.menu-item').forEach(i => i.classList.remove('drag-over'));
        dragged = null;
    });

    list.addEventListener('dragover', e => {
        e.preventDefault();
        const target = e.target.closest('.menu-item');
        if (!target || target === dragged) return;
        list.querySelectorAll('.menu-item').forEach(i => i.classList.remove('drag-over'));
        target.classList.add('drag-over');
    });

    list.addEventListener('drop', e => {
        e.preventDefault();
        const target = e.target.closest('.menu-item');
        if (!target || target === dragged || !dragged) return;
        target.classList.remove('drag-over');
        const items = [...list.querySelectorAll('.menu-item')];
        const draggedIdx = items.indexOf(dragged);
        const targetIdx  = items.indexOf(target);
        if (draggedIdx < targetIdx) {
            target.after(dragged);
        } else {
            target.before(dragged);
        }
    });
}

// -----------------------------------------------------------------------
// Add item
// -----------------------------------------------------------------------
function addItem(loc) {
    const label  = document.getElementById('add-label-' + loc).value.trim();
    const url    = document.getElementById('add-url-' + loc).value.trim();
    const target = document.getElementById('add-target-' + loc).value;

    if (!label || !url) {
        showFlash('Please enter both a label and a URL.', 'error');
        return;
    }

    const list = document.getElementById('menu-list-' + loc);

    // Remove empty placeholder if present
    const placeholder = list.querySelector('.empty-placeholder');
    if (placeholder) placeholder.remove();

    const li = buildItemEl(label, url, target);
    list.appendChild(li);

    // Reset form
    document.getElementById('add-label-' + loc).value  = '';
    document.getElementById('add-url-' + loc).value    = '';
    document.getElementById('add-target-' + loc).value = '_self';

    initSortable(list);
}

function buildItemEl(label, url, target) {
    const li = document.createElement('li');
    li.className = 'menu-item flex items-center gap-3 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5';
    li.draggable = true;
    li.dataset.id = '0';

    const targetLabel = target === '_blank' ? 'new tab' : 'same tab';

    li.innerHTML = `
        <span class="drag-handle text-slate-400 select-none" title="Drag to reorder">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/>
            </svg>
        </span>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-slate-800 truncate item-label">${escHtml(label)}</p>
            <p class="text-xs text-slate-400 truncate item-url">${escHtml(url)}</p>
        </div>
        <span class="text-xs text-slate-400 shrink-0 item-target">${escHtml(targetLabel)}</span>
        <button type="button"
                onclick="deleteItem(this)"
                class="shrink-0 rounded p-1 text-slate-400 hover:text-red-500 hover:bg-red-50 transition"
                title="Remove item">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
        <span class="hidden item-data"
              data-label="${escAttr(label)}"
              data-url="${escAttr(url)}"
              data-target="${escAttr(target)}"></span>
    `;

    li.addEventListener('dragstart', () => setTimeout(() => li.classList.add('dragging'), 0));
    li.addEventListener('dragend',   () => li.classList.remove('dragging'));

    return li;
}

// -----------------------------------------------------------------------
// Delete item
// -----------------------------------------------------------------------
function deleteItem(btn) {
    const li = btn.closest('.menu-item');
    const list = li.closest('ul');
    li.remove();
    if (!list.querySelector('.menu-item')) {
        const placeholder = document.createElement('li');
        placeholder.className = 'text-sm text-slate-400 italic py-2 empty-placeholder';
        placeholder.textContent = 'No items yet. Add one below.';
        list.appendChild(placeholder);
    }
}

// -----------------------------------------------------------------------
// Add page directly from page button
// -----------------------------------------------------------------------
function fillFromPage(loc, label, url) {
    const list = document.getElementById('menu-list-' + loc);

    const placeholder = list.querySelector('.empty-placeholder');
    if (placeholder) placeholder.remove();

    const li = buildItemEl(label, url, '_self');
    list.appendChild(li);
    initSortable(list);

    // Flash the new item briefly so the user sees it was added
    li.style.transition = 'background 0.4s';
    li.style.background = '#eef2ff';
    setTimeout(() => { li.style.background = ''; }, 600);
}

// -----------------------------------------------------------------------
// Save menu via AJAX
// -----------------------------------------------------------------------
function saveMenu(loc) {
    const list  = document.getElementById('menu-list-' + loc);
    const items = [...list.querySelectorAll('.menu-item')].map(li => {
        const dataEl = li.querySelector('.item-data');
        return {
            label:  dataEl ? dataEl.dataset.label  : li.querySelector('.item-label').textContent.trim(),
            url:    dataEl ? dataEl.dataset.url    : li.querySelector('.item-url').textContent.trim(),
            target: dataEl ? dataEl.dataset.target : '_self',
        };
    });

    const body = new URLSearchParams({
        csrf:     CSRF_TOKEN,
        location: loc,
        name:     loc,
        items:    JSON.stringify(items),
    });

    fetch(SITE_URL + '/admin/ajax.php?action=save_menu', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: body.toString(),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showFlash('Menu saved successfully.', 'success');
        } else {
            showFlash(data.message || 'Failed to save menu.', 'error');
        }
    })
    .catch(() => showFlash('Network error. Please try again.', 'error'));
}

// -----------------------------------------------------------------------
// Flash messages
// -----------------------------------------------------------------------
function showFlash(message, type) {
    const el = document.getElementById('flash');
    el.textContent = message;
    el.className = 'mb-6 rounded-lg px-4 py-3 text-sm font-medium ' +
        (type === 'success'
            ? 'bg-green-50 border border-green-200 text-green-700'
            : 'bg-red-50 border border-red-200 text-red-700');
    el.classList.remove('hidden');
    el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    setTimeout(() => el.classList.add('hidden'), 5000);
}

// -----------------------------------------------------------------------
// Helpers
// -----------------------------------------------------------------------
function escHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}
function escAttr(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/"/g, '&quot;');
}
</script>

</body>
</html>
