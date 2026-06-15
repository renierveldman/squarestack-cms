<?php
require_once dirname(__DIR__) . '/config.php';
require_once CORE_PATH . '/Database.php';
require_once CORE_PATH . '/Auth.php';
require_once CORE_PATH . '/CMS.php';
require_once CORE_PATH . '/Settings.php';

Auth::require();
$user = Auth::currentUser();

$forms = CMS::getForms();
$csrf  = Auth::generateCsrf();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forms &mdash; SquareStack CMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body class="bg-gray-100">

<div class="flex min-h-screen w-full">

<?php $currentPage = 'forms'; ?>
<?php require __DIR__ . '/partials/sidebar.php'; ?>

<div class="flex-1 flex flex-col min-h-screen">

    <header class="bg-white border-b border-gray-200 px-8 py-4 flex items-center justify-between sticky top-0 z-20">
        <div>
            <h1 class="text-xl font-semibold text-gray-800">Forms</h1>
            <p class="text-sm text-gray-500 mt-0.5">Build and manage contact forms</p>
        </div>
        <a href="<?= SITE_URL ?>/admin/forms-edit.php"
           class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
            <i class="fa-solid fa-plus text-xs"></i> New Form
        </a>
    </header>

    <main class="flex-1 px-8 py-8">

        <?php if (empty($forms)): ?>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 py-20 text-center">
            <i class="fa-solid fa-wpforms text-4xl text-gray-300 mb-4"></i>
            <p class="text-gray-500 text-sm font-medium">No forms yet</p>
            <p class="text-gray-400 text-xs mt-1 mb-5">Create your first form to start collecting submissions.</p>
            <a href="<?= SITE_URL ?>/admin/forms-edit.php"
               class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                <i class="fa-solid fa-plus text-xs"></i> New Form
            </a>
        </div>
        <?php else: ?>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50">
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Name</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Shortcode</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Fields</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Submissions</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Notify</th>
                        <th class="px-6 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach ($forms as $form):
                        $fields      = json_decode($form['fields'] ?? '[]', true) ?: [];
                        $fieldCount  = count($fields);
                        $subCount    = (int) ($form['submission_count'] ?? 0);
                        $hasMailchimp = !empty($form['mailchimp_list_id']);
                    ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="font-medium text-gray-900"><?= htmlspecialchars($form['name']) ?></div>
                        </td>
                        <td class="px-6 py-4">
                            <code class="text-xs bg-gray-100 text-indigo-700 px-2 py-1 rounded font-mono">render_form('<?= htmlspecialchars($form['slug']) ?>')</code>
                        </td>
                        <td class="px-6 py-4 text-gray-500"><?= $fieldCount ?> field<?= $fieldCount !== 1 ? 's' : '' ?></td>
                        <td class="px-6 py-4">
                            <?php if ($subCount > 0): ?>
                            <a href="<?= SITE_URL ?>/admin/forms-submissions.php?id=<?= $form['id'] ?>"
                               class="inline-flex items-center gap-1 text-indigo-600 hover:text-indigo-800 font-medium transition">
                                <?= $subCount ?> <i class="fa-solid fa-arrow-right text-xs"></i>
                            </a>
                            <?php else: ?>
                            <span class="text-gray-400">0</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4">
                            <?php if ($form['notify_email']): ?>
                                <span class="inline-flex items-center gap-1 text-xs text-green-700 bg-green-50 border border-green-200 rounded-full px-2 py-0.5">
                                    <i class="fa-solid fa-check"></i> <?= htmlspecialchars($form['notify_email']) ?>
                                </span>
                            <?php else: ?>
                                <span class="text-xs text-gray-400">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <?php if ($subCount > 0): ?>
                                <a href="<?= SITE_URL ?>/admin/forms-submissions.php?id=<?= $form['id'] ?>"
                                   class="inline-flex items-center gap-1.5 text-xs text-gray-600 hover:text-indigo-600 bg-gray-100 hover:bg-indigo-50 border border-gray-200 hover:border-indigo-200 px-2.5 py-1.5 rounded-lg transition">
                                    <i class="fa-solid fa-inbox"></i> Submissions
                                </a>
                                <?php endif; ?>
                                <a href="<?= SITE_URL ?>/admin/forms-edit.php?id=<?= $form['id'] ?>"
                                   class="inline-flex items-center gap-1.5 text-xs text-gray-600 hover:text-indigo-600 bg-gray-100 hover:bg-indigo-50 border border-gray-200 hover:border-indigo-200 px-2.5 py-1.5 rounded-lg transition">
                                    <i class="fa-solid fa-pen"></i> Edit
                                </a>
                                <button type="button"
                                        onclick="deleteForm(<?= $form['id'] ?>, <?= htmlspecialchars(json_encode($form['name'])) ?>)"
                                        class="inline-flex items-center gap-1.5 text-xs text-gray-600 hover:text-red-600 bg-gray-100 hover:bg-red-50 border border-gray-200 hover:border-red-200 px-2.5 py-1.5 rounded-lg transition">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

    </main>
</div>

</div>

<script>
function deleteForm(id, name) {
    if (!confirm('Delete "' + name + '"? All submissions will also be deleted. This cannot be undone.')) return;
    fetch('<?= SITE_URL ?>/admin/ajax.php?action=delete_form', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id=' + id + '&csrf=<?= htmlspecialchars($csrf) ?>'
    })
    .then(r => r.json())
    .then(data => { if (data.success) location.reload(); else alert(data.error || 'Delete failed.'); });
}
</script>

</body>
</html>
