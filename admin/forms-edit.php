<?php
require_once dirname(__DIR__) . '/config.php';
require_once CORE_PATH . '/Database.php';
require_once CORE_PATH . '/Auth.php';
require_once CORE_PATH . '/CMS.php';
require_once CORE_PATH . '/Settings.php';
require_once CORE_PATH . '/Slug.php';

Auth::require();
$user = Auth::currentUser();

$formId  = (int) ($_GET['id'] ?? 0);
$form    = $formId ? CMS::getForm($formId) : null;
$isNew   = !$form;

$errors  = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf']) || !Auth::verifyCsrf($_POST['csrf'])) {
        $errors[] = 'Invalid security token.';
    } else {
        $name           = trim($_POST['name']            ?? '');
        $slug           = trim($_POST['slug']            ?? '');
        $notifyEmail    = trim($_POST['notify_email']    ?? '');
        $notifySubject  = trim($_POST['notify_subject']  ?? '');
        $successMessage = trim($_POST['success_message'] ?? '');
        $redirectUrl    = trim($_POST['redirect_url']    ?? '');
        $mailchimpListId = trim($_POST['mailchimp_list_id'] ?? '');
        $fieldsJson     = $_POST['fields_json'] ?? '[]';

        if ($name === '') $errors[] = 'Form name is required.';

        if (empty($errors)) {
            if ($slug === '') $slug = Slug::generate($name);

            // Validate fields JSON
            $fieldsArr = json_decode($fieldsJson, true);
            if (!is_array($fieldsArr)) $fieldsArr = [];

            // Clean field data
            foreach ($fieldsArr as &$f) {
                $f['name']        = preg_replace('/[^a-z0-9_]/', '', strtolower($f['name'] ?? ''));
                $f['label']       = trim($f['label']       ?? '');
                $f['placeholder'] = trim($f['placeholder'] ?? '');
                $f['required']    = !empty($f['required']);
                $f['options']     = $f['options'] ?? [];
                if (is_string($f['options'])) {
                    $f['options'] = array_values(array_filter(array_map('trim', explode("\n", $f['options']))));
                }
            }
            unset($f);

            $data = [
                'name'             => $name,
                'slug'             => $slug,
                'fields'           => json_encode(array_values($fieldsArr)),
                'notify_email'     => $notifyEmail,
                'notify_subject'   => $notifySubject,
                'success_message'  => $successMessage,
                'redirect_url'     => $redirectUrl,
                'mailchimp_list_id' => $mailchimpListId,
            ];
            if ($formId) $data['id'] = $formId;

            $savedId = CMS::saveForm($data);
            header('Location: ' . SITE_URL . '/admin/forms-edit.php?id=' . $savedId . '&saved=1');
            exit;
        }
    }
}

$saved = isset($_GET['saved']);

// Load Mailchimp info
$mailchimpConnected = (Settings::get('mailchimp_api_key', '') !== '');
$mailchimpAccount   = Settings::get('mailchimp_account_name', '');

$currentFields = [];
if ($form) {
    $currentFields = json_decode($form['fields'] ?? '[]', true) ?: [];
}

$csrf = Auth::generateCsrf();

$typeIcons = [
    'text'     => 'fa-font',
    'email'    => 'fa-envelope',
    'tel'      => 'fa-phone',
    'textarea' => 'fa-align-left',
    'select'   => 'fa-list',
    'checkbox' => 'fa-square-check',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isNew ? 'New Form' : 'Edit Form' ?> &mdash; SquareStack CMS</title>
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
            <a href="<?= SITE_URL ?>/admin/forms.php"
               class="text-gray-400 hover:text-gray-700 transition">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-xl font-semibold text-gray-800"><?= $isNew ? 'New Form' : 'Edit Form' ?></h1>
                <p class="text-sm text-gray-500 mt-0.5"><?= $isNew ? 'Build your form fields and configure actions' : htmlspecialchars($form['name']) ?></p>
            </div>
        </div>
        <button type="submit" form="formEditor"
                class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
            <i class="fa-solid fa-floppy-disk"></i> Save Form
        </button>
    </header>

    <main class="flex-1 px-8 py-8">

        <?php if ($saved): ?>
        <div class="mb-6 bg-green-50 border border-green-200 text-green-700 rounded-xl px-5 py-3 text-sm flex items-center gap-2">
            <i class="fa-solid fa-check-circle"></i> Form saved successfully.
        </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
        <div class="mb-6 bg-red-50 border border-red-200 text-red-700 rounded-xl px-5 py-3 text-sm flex items-start gap-2">
            <i class="fa-solid fa-circle-exclamation mt-0.5 flex-shrink-0"></i>
            <div><?= htmlspecialchars($errors[0]) ?></div>
        </div>
        <?php endif; ?>

        <form id="formEditor" method="post" action="<?= SITE_URL ?>/admin/forms-edit.php<?= $formId ? '?id=' . $formId : '' ?>" onsubmit="serializeFields()">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
            <input type="hidden" name="fields_json" id="fields_json" value="<?= htmlspecialchars(json_encode($currentFields)) ?>">

            <div class="grid grid-cols-3 gap-6">

                <!-- Left column: fields builder -->
                <div class="col-span-2 space-y-6">

                    <!-- Form details -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-2">
                            <i class="fa-solid fa-circle-info text-indigo-500"></i>
                            <span class="font-semibold text-gray-800">Form Details</span>
                        </div>
                        <div class="px-6 py-5 space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Form Name <span class="text-red-500">*</span></label>
                                    <input type="text" id="name" name="name"
                                           value="<?= htmlspecialchars($form['name'] ?? '') ?>"
                                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                           placeholder="Contact Form"
                                           oninput="autoSlug(this.value)">
                                </div>
                                <div>
                                    <label for="slug" class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                                    <input type="text" id="slug" name="slug"
                                           value="<?= htmlspecialchars($form['slug'] ?? '') ?>"
                                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 font-mono"
                                           placeholder="contact-form">
                                    <p class="text-xs text-gray-400 mt-1">Used in <code class="bg-gray-100 px-1 rounded">render_form('slug')</code></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Field builder -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-table-list text-indigo-500"></i>
                                <span class="font-semibold text-gray-800">Fields</span>
                                <span id="fieldCount" class="text-xs text-gray-400 font-normal"></span>
                            </div>
                        </div>

                        <div id="fieldsList" class="px-4 py-4 space-y-3 min-h-[60px]">
                            <!-- Populated by JS -->
                        </div>

                        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
                            <p class="text-xs text-gray-500 mb-3 font-medium">Add field:</p>
                            <div class="flex flex-wrap gap-2">
                                <?php foreach ([
                                    ['text',     'fa-font',         'Text'],
                                    ['email',    'fa-envelope',     'Email'],
                                    ['tel',      'fa-phone',        'Phone'],
                                    ['textarea', 'fa-align-left',   'Textarea'],
                                    ['select',   'fa-list',         'Select'],
                                    ['checkbox', 'fa-square-check', 'Checkbox'],
                                ] as [$type, $icon, $label]): ?>
                                <button type="button" onclick="addField('<?= $type ?>')"
                                        class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-700 bg-white hover:bg-indigo-50 hover:text-indigo-700 border border-gray-300 hover:border-indigo-300 px-3 py-1.5 rounded-lg transition">
                                    <i class="fa-solid <?= $icon ?> text-gray-400"></i> <?= $label ?>
                                </button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Right column: settings -->
                <div class="space-y-6">

                    <!-- Notification -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
                            <i class="fa-solid fa-bell text-indigo-500"></i>
                            <span class="font-semibold text-gray-800">Notifications</span>
                        </div>
                        <div class="px-5 py-4 space-y-4">
                            <div>
                                <label for="notify_email" class="block text-sm font-medium text-gray-700 mb-1">Notify Email</label>
                                <input type="email" id="notify_email" name="notify_email"
                                       value="<?= htmlspecialchars($form['notify_email'] ?? '') ?>"
                                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                       placeholder="you@example.com">
                                <p class="text-xs text-gray-400 mt-1">Receives an email on each submission.</p>
                            </div>
                            <div>
                                <label for="notify_subject" class="block text-sm font-medium text-gray-700 mb-1">Email Subject</label>
                                <input type="text" id="notify_subject" name="notify_subject"
                                       value="<?= htmlspecialchars($form['notify_subject'] ?? '') ?>"
                                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                       placeholder="New contact form submission">
                            </div>
                        </div>
                    </div>

                    <!-- After submit -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
                            <i class="fa-solid fa-circle-check text-indigo-500"></i>
                            <span class="font-semibold text-gray-800">After Submit</span>
                        </div>
                        <div class="px-5 py-4 space-y-4">
                            <div>
                                <label for="success_message" class="block text-sm font-medium text-gray-700 mb-1">Success Message</label>
                                <textarea id="success_message" name="success_message" rows="3"
                                          class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 resize-none"
                                          placeholder="Thank you! Your message has been sent."><?= htmlspecialchars($form['success_message'] ?? '') ?></textarea>
                            </div>
                            <div>
                                <label for="redirect_url" class="block text-sm font-medium text-gray-700 mb-1">Redirect URL <span class="text-gray-400 font-normal">(optional)</span></label>
                                <input type="text" id="redirect_url" name="redirect_url"
                                       value="<?= htmlspecialchars($form['redirect_url'] ?? '') ?>"
                                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                       placeholder="/thank-you">
                                <p class="text-xs text-gray-400 mt-1">If set, overrides the success message.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Mailchimp -->
                    <?php if ($mailchimpConnected): ?>
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
                            <i class="fa-solid fa-envelope-open-text text-yellow-500"></i>
                            <span class="font-semibold text-gray-800">Mailchimp</span>
                        </div>
                        <div class="px-5 py-4 space-y-3">
                            <p class="text-xs text-gray-500">Connected as <strong><?= htmlspecialchars($mailchimpAccount) ?></strong>. Select an audience to subscribe the email field on submission.</p>
                            <div>
                                <label for="mailchimp_list_id" class="block text-sm font-medium text-gray-700 mb-1">Audience</label>
                                <select id="mailchimp_list_id" name="mailchimp_list_id"
                                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">— None —</option>
                                </select>
                                <p class="text-xs text-gray-400 mt-1" id="mailchimpListStatus">Loading audiences&hellip;</p>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
                            <i class="fa-solid fa-envelope-open-text text-gray-400"></i>
                            <span class="font-semibold text-gray-800">Mailchimp</span>
                        </div>
                        <div class="px-5 py-4">
                            <p class="text-sm text-gray-500">Connect Mailchimp in <a href="<?= SITE_URL ?>/admin/integrations.php" class="text-indigo-600 hover:underline">Integrations</a> to enable audience subscription.</p>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Shortcode -->
                    <?php if (!$isNew): ?>
                    <div class="bg-indigo-50 border border-indigo-200 rounded-xl px-5 py-4">
                        <p class="text-xs font-semibold text-indigo-700 mb-2 uppercase tracking-wide">Use in templates</p>
                        <code class="text-xs text-indigo-800 font-mono break-all">&lt;?php render_form('<?= htmlspecialchars($form['slug']) ?>'); ?&gt;</code>
                    </div>
                    <?php endif; ?>

                </div>
            </div>
        </form>
    </main>
</div>

</div>

<script>
/* ---- Field state ---- */
var fields = <?= json_encode($currentFields, JSON_HEX_TAG | JSON_HEX_APOS) ?>;
var fieldIdCounter = fields.length;

var TYPE_ICONS = {
    text:     'fa-font',
    email:    'fa-envelope',
    tel:      'fa-phone',
    textarea: 'fa-align-left',
    select:   'fa-list',
    checkbox: 'fa-square-check'
};

function createField(type) {
    fieldIdCounter++;
    return {
        _id:         '_f' + fieldIdCounter,
        type:        type || 'text',
        label:       '',
        name:        '',
        placeholder: '',
        required:    false,
        options:     []
    };
}

function addField(type) {
    fields.push(createField(type));
    renderFields();
    var list = document.getElementById('fieldsList');
    list.lastElementChild && list.lastElementChild.querySelector('input[data-role="label"]').focus();
}

function removeField(id) {
    if (!confirm('Remove this field?')) return;
    fields = fields.filter(function(f) { return f._id !== id; });
    renderFields();
}

function moveField(id, dir) {
    var idx = fields.findIndex(function(f) { return f._id === id; });
    if (dir === 'up' && idx > 0) {
        var tmp = fields[idx]; fields[idx] = fields[idx - 1]; fields[idx - 1] = tmp;
    } else if (dir === 'down' && idx < fields.length - 1) {
        var tmp = fields[idx]; fields[idx] = fields[idx + 1]; fields[idx + 1] = tmp;
    }
    renderFields();
}

function syncField(id, key, value) {
    var f = fields.find(function(f) { return f._id === id; });
    if (!f) return;
    if (key === 'required') {
        f.required = value;
    } else if (key === 'type') {
        f.type = value;
        // Re-render this card to show/hide options
        renderFields();
        return;
    } else {
        f[key] = value;
        if (key === 'label' && !f._nameEdited) {
            f.name = labelToName(value);
            // Update the name input live
            var card = document.querySelector('[data-field-id="' + id + '"]');
            if (card) {
                var nameInput = card.querySelector('[data-role="name"]');
                if (nameInput) nameInput.value = f.name;
            }
        }
    }
}

function labelToName(label) {
    return label.toLowerCase()
        .replace(/[^a-z0-9\s]/g, '')
        .trim()
        .replace(/\s+/g, '_')
        .substring(0, 64);
}

function renderFields() {
    var list = document.getElementById('fieldsList');
    var scroll = list.scrollTop;
    list.innerHTML = '';

    if (fields.length === 0) {
        list.innerHTML = '<p class="text-center text-sm text-gray-400 py-6">No fields yet — add one below.</p>';
        document.getElementById('fieldCount').textContent = '';
        return;
    }

    document.getElementById('fieldCount').textContent = '(' + fields.length + ')';

    fields.forEach(function(f, i) {
        // Ensure _id exists for loaded fields
        if (!f._id) f._id = '_f' + (++fieldIdCounter);

        var card = document.createElement('div');
        card.className = 'bg-white border border-gray-200 rounded-xl overflow-hidden';
        card.dataset.fieldId = f._id;

        var isFirst = i === 0;
        var isLast  = i === fields.length - 1;
        var icon    = TYPE_ICONS[f.type] || 'fa-font';
        var displayLabel = f.label || '<span class="text-gray-400 italic">Untitled</span>';

        var optionsHtml = '';
        if (f.type === 'select') {
            var optVal = Array.isArray(f.options) ? f.options.join('\n') : (f.options || '');
            optionsHtml = '<div class="col-span-2">'
                + '<label class="block text-xs font-medium text-gray-600 mb-1">Options <span class="text-gray-400 font-normal">(one per line)</span></label>'
                + '<textarea rows="4" class="w-full px-2.5 py-1.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none" '
                + 'onchange="syncField(\'' + esc(f._id) + '\', \'options\', this.value.split(\'\\n\'))">'
                + escHtml(optVal) + '</textarea>'
                + '</div>';
        }

        card.innerHTML =
            '<div class="px-4 py-2.5 bg-gray-50 border-b border-gray-200 flex items-center justify-between">'
          +   '<div class="flex items-center gap-2">'
          +     '<i class="fa-solid ' + icon + ' text-indigo-400 text-xs w-4 text-center"></i>'
          +     '<span class="text-sm font-medium text-gray-700">' + (f.label ? escHtml(f.label) : '<span class="text-gray-400 italic text-xs">Untitled</span>') + '</span>'
          +     '<span class="text-xs text-gray-400">' + f.type + '</span>'
          +   '</div>'
          +   '<div class="flex items-center gap-0.5">'
          +     (isFirst  ? '' : '<button type="button" onclick="moveField(\'' + esc(f._id) + '\', \'up\')" class="w-7 h-7 flex items-center justify-center text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded transition"><i class="fa-solid fa-chevron-up text-xs"></i></button>')
          +     (isLast   ? '' : '<button type="button" onclick="moveField(\'' + esc(f._id) + '\', \'down\')" class="w-7 h-7 flex items-center justify-center text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded transition"><i class="fa-solid fa-chevron-down text-xs"></i></button>')
          +     '<button type="button" onclick="removeField(\'' + esc(f._id) + '\')" class="w-7 h-7 flex items-center justify-center text-gray-400 hover:text-red-600 hover:bg-red-50 rounded transition"><i class="fa-solid fa-xmark text-xs"></i></button>'
          +   '</div>'
          + '</div>'
          + '<div class="px-4 py-4 grid grid-cols-2 gap-3">'
          +   '<div>'
          +     '<label class="block text-xs font-medium text-gray-600 mb-1">Type</label>'
          +     '<select class="w-full px-2.5 py-1.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" onchange="syncField(\'' + esc(f._id) + '\', \'type\', this.value)">'
          +     ['text','email','tel','textarea','select','checkbox'].map(function(t) {
                    return '<option value="' + t + '"' + (f.type === t ? ' selected' : '') + '>' + t.charAt(0).toUpperCase() + t.slice(1) + '</option>';
                }).join('')
          +     '</select>'
          +   '</div>'
          +   '<div>'
          +     '<label class="block text-xs font-medium text-gray-600 mb-1">Label</label>'
          +     '<input type="text" data-role="label" value="' + escHtml(f.label) + '" placeholder="Your label"'
          +     ' class="w-full px-2.5 py-1.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"'
          +     ' oninput="syncField(\'' + esc(f._id) + '\', \'label\', this.value)">'
          +   '</div>'
          +   '<div>'
          +     '<label class="block text-xs font-medium text-gray-600 mb-1">Field Name <span class="text-gray-400 font-normal">(HTML name)</span></label>'
          +     '<input type="text" data-role="name" value="' + escHtml(f.name) + '" placeholder="field_name"'
          +     ' class="w-full px-2.5 py-1.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono text-xs"'
          +     ' oninput="fields.find(function(x){return x._id===\'' + esc(f._id) + '\'})._nameEdited=true; syncField(\'' + esc(f._id) + '\', \'name\', this.value)">'
          +   '</div>'
          +   '<div>'
          +     '<label class="block text-xs font-medium text-gray-600 mb-1">Placeholder</label>'
          +     '<input type="text" value="' + escHtml(f.placeholder) + '" placeholder="Optional hint text"'
          +     ' class="w-full px-2.5 py-1.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"'
          +     ' oninput="syncField(\'' + esc(f._id) + '\', \'placeholder\', this.value)">'
          +   '</div>'
          +   optionsHtml
          +   '<div class="col-span-2 flex items-center gap-2">'
          +     '<input type="checkbox" id="req_' + escHtml(f._id) + '" ' + (f.required ? 'checked' : '')
          +     ' class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"'
          +     ' onchange="syncField(\'' + esc(f._id) + '\', \'required\', this.checked)">'
          +     '<label for="req_' + escHtml(f._id) + '" class="text-sm text-gray-700 cursor-pointer">Required</label>'
          +   '</div>'
          + '</div>';

        list.appendChild(card);
    });

    list.scrollTop = scroll;
}

function serializeFields() {
    // Strip internal _id and _nameEdited before saving
    var clean = fields.map(function(f) {
        return {
            type:        f.type,
            label:       f.label,
            name:        f.name,
            placeholder: f.placeholder,
            required:    f.required,
            options:     f.options
        };
    });
    document.getElementById('fields_json').value = JSON.stringify(clean);
}

function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}
function esc(s) {
    return String(s).replace(/'/g,"\\'");
}

/* ---- Slug auto-generation ---- */
var slugManuallyEdited = <?= ($form && $form['slug']) ? 'true' : 'false' ?>;
document.getElementById('slug').addEventListener('input', function() { slugManuallyEdited = true; });

function autoSlug(val) {
    if (slugManuallyEdited) return;
    document.getElementById('slug').value = val.toLowerCase()
        .replace(/[^a-z0-9\s-]/g, '').trim().replace(/\s+/g, '-');
}

/* ---- Mailchimp audience loader ---- */
<?php if ($mailchimpConnected): ?>
(function () {
    var select = document.getElementById('mailchimp_list_id');
    var status = document.getElementById('mailchimpListStatus');
    var savedListId = <?= json_encode($form['mailchimp_list_id'] ?? '') ?>;

    fetch('<?= SITE_URL ?>/admin/ajax.php?action=get_mailchimp_lists', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'csrf=<?= htmlspecialchars($csrf) ?>'
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (!data.success || !data.lists || !data.lists.length) {
            status.textContent = data.error || 'No audiences found.';
            return;
        }
        status.textContent = data.lists.length + ' audience' + (data.lists.length !== 1 ? 's' : '') + ' found.';
        data.lists.forEach(function(list) {
            var opt = document.createElement('option');
            opt.value = list.id;
            opt.textContent = list.name + ' (' + (list.stats && list.stats.member_count ? list.stats.member_count + ' contacts' : 'empty') + ')';
            if (list.id === savedListId) opt.selected = true;
            select.appendChild(opt);
        });
    })
    .catch(function() { status.textContent = 'Could not load audiences.'; });
}());
<?php endif; ?>

/* ---- Init ---- */
renderFields();
</script>

</body>
</html>
