<?php
require_once dirname(__DIR__) . '/config.php';
require_once CORE_PATH . '/Database.php';
require_once CORE_PATH . '/Auth.php';
require_once CORE_PATH . '/CMS.php';

Auth::require();
$user = Auth::currentUser();

$formId = (int) ($_GET['id'] ?? 0);
if (!$formId) {
    header('Location: ' . SITE_URL . '/admin/forms.php');
    exit;
}

$form = CMS::getForm($formId);
if (!$form) {
    header('Location: ' . SITE_URL . '/admin/forms.php');
    exit;
}

$fields = json_decode($form['fields'] ?? '[]', true) ?: [];

// Build label map: name => label
$labelMap = [];
foreach ($fields as $f) {
    if (!empty($f['name'])) {
        $labelMap[$f['name']] = $f['label'] ?? $f['name'];
    }
}

$page       = max(1, (int) ($_GET['page'] ?? 1));
$perPage    = 25;
$offset     = ($page - 1) * $perPage;
$result     = CMS::getFormSubmissions($formId, $perPage, $offset);
$submissions = $result['items'];
$total       = $result['total'];
$totalPages  = (int) ceil($total / $perPage);

$csrf = Auth::generateCsrf();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submissions: <?= htmlspecialchars($form['name']) ?> &mdash; SquareStack CMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body class="bg-gray-100">

<div class="flex min-h-screen w-full">

<?php $currentPage = 'forms'; ?>
<?php require __DIR__ . '/partials/sidebar.php'; ?>

<div class="flex-1 flex flex-col min-h-screen">

    <header class="bg-white border-b border-gray-200 px-8 py-4 flex items-center justify-between sticky top-0 z-20">
        <div class="flex items-center gap-3">
            <a href="<?= SITE_URL ?>/admin/forms.php" class="text-gray-400 hover:text-gray-700 transition">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-xl font-semibold text-gray-800">Submissions</h1>
                <p class="text-sm text-gray-500 mt-0.5"><?= htmlspecialchars($form['name']) ?> &mdash; <?= $total ?> total</p>
            </div>
        </div>
        <?php if ($total > 0): ?>
        <button type="button" onclick="exportCsv()"
                class="inline-flex items-center gap-2 text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 border border-gray-300 px-4 py-2 rounded-lg transition">
            <i class="fa-solid fa-download text-xs"></i> Export CSV
        </button>
        <?php endif; ?>
    </header>

    <main class="flex-1 px-8 py-8">

        <?php if (empty($submissions)): ?>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 py-20 text-center">
            <i class="fa-solid fa-inbox text-4xl text-gray-300 mb-4"></i>
            <p class="text-gray-500 text-sm font-medium">No submissions yet</p>
            <p class="text-gray-400 text-xs mt-1">Submissions will appear here once visitors fill in the form.</p>
        </div>
        <?php else: ?>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <table class="w-full text-sm" id="submissionsTable">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50">
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide w-40">Date</th>
                        <?php foreach ($fields as $f): ?>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide"><?= htmlspecialchars($f['label'] ?? $f['name'] ?? '') ?></th>
                        <?php endforeach; ?>
                        <th class="px-4 py-3 w-12"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach ($submissions as $sub):
                        $data = json_decode($sub['data'] ?? '{}', true) ?: [];
                    ?>
                    <tr class="hover:bg-gray-50 transition-colors" data-row='<?= htmlspecialchars(json_encode($data), ENT_QUOTES) ?>'>
                        <td class="px-6 py-3 text-gray-500 whitespace-nowrap text-xs">
                            <?= date('d M Y', strtotime($sub['created_at'])) ?><br>
                            <span class="text-gray-400"><?= date('H:i', strtotime($sub['created_at'])) ?></span>
                        </td>
                        <?php foreach ($fields as $f):
                            $key = $f['name'] ?? '';
                            $val = $data[$key] ?? '';
                        ?>
                        <td class="px-4 py-3 text-gray-700 max-w-xs truncate" title="<?= htmlspecialchars($val) ?>">
                            <?= htmlspecialchars($val ?: '—') ?>
                        </td>
                        <?php endforeach; ?>
                        <td class="px-4 py-3 text-right">
                            <button type="button"
                                    onclick="deleteSubmission(<?= $sub['id'] ?>, this)"
                                    class="text-gray-400 hover:text-red-600 transition">
                                <i class="fa-solid fa-trash text-xs"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
        <nav class="flex items-center justify-center gap-2 mt-6">
            <?php if ($page > 1): ?>
            <a href="?id=<?= $formId ?>&page=<?= $page - 1 ?>"
               class="px-3 py-2 text-sm rounded-lg border border-gray-300 hover:bg-gray-50 transition">← Prev</a>
            <?php endif; ?>
            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
            <a href="?id=<?= $formId ?>&page=<?= $p ?>"
               class="px-3 py-2 text-sm rounded-lg border <?= $p === $page ? 'bg-indigo-600 text-white border-indigo-600' : 'border-gray-300 hover:bg-gray-50' ?> transition"><?= $p ?></a>
            <?php endfor; ?>
            <?php if ($page < $totalPages): ?>
            <a href="?id=<?= $formId ?>&page=<?= $page + 1 ?>"
               class="px-3 py-2 text-sm rounded-lg border border-gray-300 hover:bg-gray-50 transition">Next →</a>
            <?php endif; ?>
        </nav>
        <?php endif; ?>

        <?php endif; ?>
    </main>
</div>
</div>

<script>
var CSRF = '<?= htmlspecialchars($csrf) ?>';
var AJAX = '<?= SITE_URL ?>/admin/ajax.php';

function deleteSubmission(id, btn) {
    if (!confirm('Delete this submission?')) return;
    fetch(AJAX + '?action=delete_form_submission', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id=' + id + '&csrf=' + encodeURIComponent(CSRF)
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) btn.closest('tr').remove();
        else alert(data.error || 'Delete failed.');
    });
}

function exportCsv() {
    var table = document.getElementById('submissionsTable');
    var rows  = table.querySelectorAll('tr');
    var csv   = [];
    rows.forEach(function(row) {
        var cells = row.querySelectorAll('th, td');
        var line  = [];
        cells.forEach(function(cell, i) {
            if (i === cells.length - 1) return; // skip delete button column
            var text = cell.getAttribute('title') || cell.innerText.replace(/\s+/g, ' ').trim();
            line.push('"' + text.replace(/"/g, '""') + '"');
        });
        csv.push(line.join(','));
    });
    var blob = new Blob([csv.join('\n')], { type: 'text/csv' });
    var a    = document.createElement('a');
    a.href   = URL.createObjectURL(blob);
    a.download = '<?= htmlspecialchars(preg_replace('/[^a-z0-9-]/', '-', strtolower($form['slug']))) ?>-submissions.csv';
    a.click();
}
</script>

</body>
</html>
