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

$id      = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$post    = $id ? CMS::getPost($id) : [];
$isEdit  = !empty($post);
$success = false;
$error   = '';

$categories = CMS::getCategories();
$meta       = $isEdit ? CMS::getAllMeta('post', $id) : [];

// Bootstrap theme functions for field groups
$themeFunctions = THEME_PATH . '/functions.php';
if (file_exists($themeFunctions)) require_once $themeFunctions;

$fieldGroups = CMS::getFieldGroups('post', '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::verifyCsrf($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid CSRF token.';
    } else {
        $pubAt = !empty($_POST['published_at']) ? date('Y-m-d H:i:s', strtotime($_POST['published_at'])) : null;
        $data = [
            'title'            => trim($_POST['title'] ?? ''),
            'slug'             => Slug::unique(
                                    Slug::generate($_POST['slug'] ?: $_POST['title']),
                                    'posts', 'slug', $id),
            'content'          => $_POST['content'] ?? '',
            'excerpt'          => trim($_POST['excerpt'] ?? ''),
            'featured_image'   => trim($_POST['featured_image'] ?? ''),
            'category_id'      => !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null,
            'status'           => $_POST['status'] ?? 'draft',
            'meta_title'       => trim($_POST['meta_title'] ?? ''),
            'meta_description' => trim($_POST['meta_description'] ?? ''),
            'og_image'         => trim($_POST['og_image'] ?? ''),
            'published_at'     => $pubAt,
        ];
        if ($isEdit) $data['id'] = $id;

        $savedId = CMS::savePost($data);

        foreach ($_POST as $k => $v) {
            if (str_starts_with($k, 'cf_')) {
                CMS::saveMeta('post', $savedId, substr($k, 3), $v);
            }
        }
        Cache::flush();

        if (!$isEdit) {
            header('Location: ' . SITE_URL . '/admin/posts-edit.php?id=' . $savedId . '&saved=1');
            exit;
        }
        $success = true;
        $post    = CMS::getPost($savedId);
        $meta    = CMS::getAllMeta('post', $savedId);
    }
}

$csrf      = Auth::generateCsrf();
$pageTitle = $isEdit ? 'Edit Post: ' . ($post['title'] ?? '') : 'Add New Post';
$user      = Auth::currentUser();
$blogSlug  = Settings::get('blog_slug', 'blog');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle) ?> — SquareStack Admin</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
tinymce.init({
    selector: '#content',
    plugins: 'link lists image code table',
    toolbar: 'undo redo | blocks | bold italic | alignleft aligncenter alignright | bullist numlist | link image | code',
    height: 450,
    menubar: false,
    branding: false,
    promotion: false,
    setup: function(editor) {
        editor.on('input change', function() { editor.save(); updateSeo(); });
    }
});
</script>
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
            <a href="<?= SITE_URL ?>/admin/posts.php" class="flex items-center gap-3 px-3 py-2 rounded-lg bg-slate-700 text-white text-sm font-medium"><i class="fa fa-newspaper w-4"></i> Posts</a>
            <a href="<?= SITE_URL ?>/admin/menus.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-slate-300 hover:bg-slate-800 hover:text-white text-sm"><i class="fa fa-bars w-4"></i> Menus</a>
            <a href="<?= SITE_URL ?>/admin/media.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-slate-300 hover:bg-slate-800 hover:text-white text-sm"><i class="fa fa-images w-4"></i> Media</a>
            <a href="<?= SITE_URL ?>/admin/settings.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-slate-300 hover:bg-slate-800 hover:text-white text-sm"><i class="fa fa-gear w-4"></i> Settings</a>
        </nav>
        <div class="p-4 border-t border-slate-700">
            <p class="text-slate-400 text-xs mb-2"><?= htmlspecialchars($user['name'] ?? '') ?></p>
            <a href="<?= SITE_URL ?>/admin/logout.php" class="text-slate-400 hover:text-white text-xs"><i class="fa fa-right-from-bracket mr-1"></i>Logout</a>
        </div>
    </aside>

    <!-- Main -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white border-b border-gray-200 px-8 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="<?= SITE_URL ?>/admin/posts.php" class="text-gray-500 hover:text-gray-700"><i class="fa fa-arrow-left"></i></a>
                <h1 class="text-xl font-semibold"><?= htmlspecialchars($pageTitle) ?></h1>
            </div>
            <?php if ($isEdit && ($post['status'] ?? '') === 'published'): ?>
            <a href="<?= SITE_URL ?>/<?= $blogSlug ?>/<?= htmlspecialchars($post['slug'] ?? '') ?>" target="_blank" class="text-sm text-indigo-600 hover:underline"><i class="fa fa-external-link mr-1"></i>View Post</a>
            <?php endif; ?>
        </header>

        <?php if ($success): ?>
        <div class="bg-green-50 border-l-4 border-green-400 p-4 mx-8 mt-4 text-green-800 text-sm rounded">Post saved successfully.</div>
        <?php endif; ?>
        <?php if ($error): ?>
        <div class="bg-red-50 border-l-4 border-red-400 p-4 mx-8 mt-4 text-red-800 text-sm rounded"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['saved'])): ?>
        <div class="bg-green-50 border-l-4 border-green-400 p-4 mx-8 mt-4 text-green-800 text-sm rounded">Post created successfully.</div>
        <?php endif; ?>

        <form method="POST" class="flex-1 flex overflow-hidden">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">

        <div class="flex-1 overflow-y-auto p-8 space-y-6">

            <!-- Title -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Title <span class="text-red-500">*</span></label>
                <input type="text" name="title" id="post-title" required
                    value="<?= htmlspecialchars($post['title'] ?? '') ?>"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-lg font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    oninput="autoSlug(this.value)">
            </div>

            <!-- Slug -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                <div class="flex items-center gap-2">
                    <span class="text-gray-400 text-sm"><?= SITE_URL ?>/<?= htmlspecialchars($blogSlug) ?>/</span>
                    <input type="text" name="slug" id="post-slug"
                        value="<?= htmlspecialchars($post['slug'] ?? '') ?>"
                        class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <!-- Featured Image -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Featured Image</label>
                <?php $fi = $post['featured_image'] ?? ''; ?>
                <?php if ($fi): ?>
                <img src="<?= htmlspecialchars($fi) ?>" class="w-full max-h-48 object-cover rounded-lg mb-2" id="featured-preview">
                <?php else: ?>
                <div id="featured-preview" class="hidden w-full max-h-48 object-cover rounded-lg mb-2 overflow-hidden"><img class="w-full h-48 object-cover"></div>
                <?php endif; ?>
                <div class="flex gap-2">
                    <input type="text" name="featured_image" id="featured-image"
                        value="<?= htmlspecialchars($fi) ?>"
                        class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        oninput="updateFeaturedPreview(this.value)">
                    <button type="button" onclick="openMediaPicker('featured_image')" class="px-3 py-2 bg-gray-100 border border-gray-300 rounded-lg text-sm hover:bg-gray-200">Browse</button>
                </div>
            </div>

            <!-- Excerpt -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Excerpt</label>
                <textarea name="excerpt" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"><?= htmlspecialchars($post['excerpt'] ?? '') ?></textarea>
            </div>

            <!-- Content -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Content</label>
                <textarea name="content" id="content"><?= htmlspecialchars($post['content'] ?? '') ?></textarea>
            </div>

            <!-- SEO -->
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="font-semibold text-gray-800 mb-4">SEO</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Meta Title <span id="title-count" class="text-gray-400 font-normal ml-1 text-xs"></span></label>
                        <input type="text" name="meta_title" id="meta-title"
                            value="<?= htmlspecialchars($post['meta_title'] ?? '') ?>"
                            oninput="updateSeo()"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Meta Description <span id="desc-count" class="text-gray-400 font-normal ml-1 text-xs"></span></label>
                        <textarea name="meta_description" id="meta-desc" rows="3" oninput="updateSeo()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"><?= htmlspecialchars($post['meta_description'] ?? '') ?></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">OG Image</label>
                        <div class="flex gap-2">
                            <input type="text" name="og_image" id="og-image"
                                value="<?= htmlspecialchars($post['og_image'] ?? '') ?>"
                                oninput="updateSeo()"
                                class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <button type="button" onclick="openMediaPicker('og_image')" class="px-3 py-2 bg-gray-100 border border-gray-300 rounded-lg text-sm hover:bg-gray-200">Browse</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SEO Analyzer -->
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="font-semibold text-gray-800 mb-4">SEO Analyzer</h3>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Focus Keyphrase</label>
                    <input type="text" id="focus-kp" oninput="updateSeo()" placeholder="e.g. custom PHP CMS"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="flex items-start gap-6">
                    <div class="flex-shrink-0">
                        <svg width="80" height="80" viewBox="0 0 80 80">
                            <circle cx="40" cy="40" r="34" fill="none" stroke="#e5e7eb" stroke-width="8"/>
                            <circle id="score-ring" cx="40" cy="40" r="34" fill="none" stroke="#6366f1" stroke-width="8"
                                stroke-dasharray="213.6" stroke-dashoffset="213.6"
                                stroke-linecap="round" transform="rotate(-90 40 40)"
                                style="transition: stroke-dashoffset 0.5s, stroke 0.5s"/>
                        </svg>
                        <p class="text-center text-xl font-bold mt-1" id="score-num">0</p>
                    </div>
                    <div class="flex-1 space-y-1 text-sm" id="seo-checks"></div>
                </div>
                <div class="mt-6 p-4 border border-gray-200 rounded-lg bg-gray-50">
                    <p class="text-xs text-gray-500 mb-2 font-medium uppercase tracking-wide">SERP Preview</p>
                    <p id="serp-title" class="text-blue-700 text-lg leading-tight truncate font-medium"></p>
                    <p id="serp-url" class="text-green-700 text-xs my-0.5"></p>
                    <p id="serp-desc" class="text-gray-600 text-sm leading-snug"></p>
                </div>
            </div>

        </div>

        <!-- Right Sidebar -->
        <aside class="w-72 bg-white border-l border-gray-200 overflow-y-auto p-6 space-y-6 flex-shrink-0">
            <div>
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 px-4 rounded-lg transition-colors">
                    <i class="fa fa-save mr-2"></i><?= $isEdit ? 'Update Post' : 'Publish Post' ?>
                </button>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="draft" <?= ($post['status'] ?? 'draft') === 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="published" <?= ($post['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                <select name="category_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">— No Category —</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= ($post['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Published Date</label>
                <input type="datetime-local" name="published_at"
                    value="<?= $post['published_at'] ? date('Y-m-d\TH:i', strtotime($post['published_at'])) : '' ?>"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <?php if ($isEdit): ?>
            <div class="border-t border-gray-200 pt-4">
                <p class="text-xs text-gray-500 mb-1">Created</p>
                <p class="text-sm"><?= date('M j, Y', strtotime($post['created_at'] ?? 'now')) ?></p>
            </div>
            <div class="border-t border-gray-200 pt-4">
                <button type="button"
                    onclick="if(confirm('Delete this post?')) { fetch('<?= SITE_URL ?>/admin/ajax.php?action=delete_post', {method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'id=<?= $id ?>&csrf_token=<?= $csrf ?>'}).then(()=>window.location='<?= SITE_URL ?>/admin/posts.php') }"
                    class="text-red-600 hover:text-red-800 text-sm"><i class="fa fa-trash mr-1"></i>Delete Post</button>
            </div>
            <?php endif; ?>
        </aside>
        </form>
    </div>
</div>

<!-- Media Picker Modal -->
<div id="media-modal" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-xl w-full max-w-4xl max-h-[80vh] flex flex-col">
        <div class="flex items-center justify-between p-4 border-b border-gray-200">
            <h3 class="font-semibold">Select Media</h3>
            <button onclick="closeMediaPicker()" class="text-gray-500 hover:text-gray-700"><i class="fa fa-xmark text-lg"></i></button>
        </div>
        <div class="flex-1 overflow-y-auto p-4">
            <div id="media-grid" class="grid grid-cols-4 sm:grid-cols-6 gap-3"></div>
        </div>
    </div>
</div>

<script>
let mediaPickerTarget = null;

function openMediaPicker(fieldName) {
    mediaPickerTarget = fieldName;
    document.getElementById('media-modal').classList.remove('hidden');
    loadMediaGrid();
}
function closeMediaPicker() { document.getElementById('media-modal').classList.add('hidden'); }
function loadMediaGrid(page) {
    fetch('<?= SITE_URL ?>/admin/ajax.php?action=get_media&page=' + (page || 1))
        .then(r => r.json())
        .then(data => {
            document.getElementById('media-grid').innerHTML = data.items.map(item =>
                `<div class="cursor-pointer group" onclick="selectMedia('${item.url}')">
                    <div class="aspect-square bg-gray-100 rounded-lg overflow-hidden border-2 border-transparent group-hover:border-indigo-500 transition-colors">
                        <img src="${item.url}" alt="${item.alt_text||''}" class="w-full h-full object-cover">
                    </div>
                    <p class="text-xs text-gray-500 mt-1 truncate">${item.original_name}</p>
                </div>`).join('');
        });
}
function selectMedia(url) {
    if (mediaPickerTarget) {
        const el = document.querySelector('[name="'+mediaPickerTarget+'"]') || document.getElementById(mediaPickerTarget);
        if (el) { el.value = url; updateSeo(); }
        if (mediaPickerTarget === 'featured_image') updateFeaturedPreview(url);
    }
    closeMediaPicker();
}
function updateFeaturedPreview(url) {
    const el = document.getElementById('featured-preview');
    if (!el) return;
    if (url) {
        const img = el.tagName === 'IMG' ? el : el.querySelector('img');
        if (img) img.src = url;
        el.classList.remove('hidden');
    } else {
        el.classList.add('hidden');
    }
}

let slugEdited = <?= $isEdit ? 'true' : 'false' ?>;
function autoSlug(title) {
    if (!slugEdited) {
        const slug = title.toLowerCase().replace(/[^a-z0-9\s-]/g,'').trim().replace(/\s+/g,'-').replace(/-+/g,'-');
        document.getElementById('post-slug').value = slug;
    }
    updateSeo();
}
document.getElementById('post-slug')?.addEventListener('input', () => { slugEdited = true; updateSeo(); });

function updateSeo() {
    const title   = document.getElementById('meta-title')?.value || document.getElementById('post-title')?.value || '';
    const desc    = document.getElementById('meta-desc')?.value || '';
    const slug    = document.getElementById('post-slug')?.value || '';
    const kp      = (document.getElementById('focus-kp')?.value || '').toLowerCase().trim();
    const ogImage = document.getElementById('og-image')?.value || '';
    const content = tinymce.get('content')?.getContent({format:'text'}) || document.getElementById('content')?.value || '';
    const wordCount = content.trim().split(/\s+/).filter(Boolean).length;

    document.getElementById('title-count').textContent = title.length + '/60';
    document.getElementById('desc-count').textContent  = desc.length + '/160';

    const siteName = '<?= addslashes(Settings::get('site_name', 'My Website')) ?>';
    document.getElementById('serp-title').textContent = (title || 'Post Title') + ' | ' + siteName;
    document.getElementById('serp-url').textContent   = '<?= SITE_URL ?>/<?= $blogSlug ?>/' + slug;
    document.getElementById('serp-desc').textContent  = desc || 'Meta description will appear here...';

    const checks = [
        { label: 'Meta title 10–60 chars',        pass: title.length >= 10 && title.length <= 60 },
        { label: 'Meta description 50–160 chars',  pass: desc.length >= 50 && desc.length <= 160 },
        { label: 'Keyphrase in title',             pass: kp && title.toLowerCase().includes(kp), skip: !kp },
        { label: 'Keyphrase in description',       pass: kp && desc.toLowerCase().includes(kp), skip: !kp },
        { label: 'Keyphrase in slug',              pass: kp && slug.includes(kp.replace(/\s+/g,'-')), skip: !kp },
        { label: 'Keyphrase in content',           pass: kp && content.toLowerCase().includes(kp), skip: !kp },
        { label: 'Content ≥ 300 words',            pass: wordCount >= 300 },
        { label: 'OG image set',                   pass: !!ogImage },
    ];

    const passed = checks.filter(c => !c.skip && c.pass).length;
    const total  = checks.filter(c => !c.skip).length;
    const score  = total ? Math.round((passed / total) * 100) : 0;

    document.getElementById('score-num').textContent = score;
    const ring = document.getElementById('score-ring');
    const circ = 2 * Math.PI * 34;
    ring.style.strokeDasharray  = circ;
    ring.style.strokeDashoffset = circ - (circ * score / 100);
    ring.style.stroke = score >= 70 ? '#22c55e' : score >= 40 ? '#f59e0b' : '#ef4444';

    document.getElementById('seo-checks').innerHTML = checks.map(c => {
        if (c.skip) return `<div class="flex items-center gap-2 text-gray-400"><i class="fa fa-circle w-3 h-3"></i>${c.label} <em class="text-xs">(set keyphrase)</em></div>`;
        return `<div class="flex items-center gap-2 ${c.pass?'text-green-700':'text-red-600'}"><i class="fa ${c.pass?'fa-circle-check':'fa-circle-xmark'} w-3 h-3"></i>${c.label}</div>`;
    }).join('');
}

document.addEventListener('DOMContentLoaded', updateSeo);
</script>
</body>
</html>
