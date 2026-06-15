<?php
require_once dirname(__DIR__) . '/config.php';
require_once CORE_PATH . '/Database.php';
require_once CORE_PATH . '/Auth.php';
require_once CORE_PATH . '/Settings.php';

Auth::require();
$user = Auth::currentUser();

$success = [];
$errors  = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf']) || !Auth::verifyCsrf($_POST['csrf'])) {
        $errors[] = 'Invalid security token. Please try again.';
    } else {
        $section = $_POST['section'] ?? '';

        if ($section === 'ga') {
            Settings::set('ga_id', trim($_POST['ga_id'] ?? ''));
            $success[] = 'ga';
        }

        if ($section === 'gtm') {
            Settings::set('gtm_id', trim($_POST['gtm_id'] ?? ''));
            $success[] = 'gtm';
        }

        if ($section === 'meta_pixel') {
            Settings::set('meta_pixel_id', trim($_POST['meta_pixel_id'] ?? ''));
            $success[] = 'meta_pixel';
        }

        if ($section === 'gsc') {
            Settings::set('gsc_verification', trim($_POST['gsc_verification'] ?? ''));
            $success[] = 'gsc';
        }

        if ($section === 'mailchimp') {
            $apiKey = trim($_POST['mailchimp_api_key'] ?? '');
            if ($apiKey === '') {
                $errors[] = 'Please enter a Mailchimp API key.';
            } else {
                $result = mailchimpVerifyKey($apiKey);
                if (!$result['success']) {
                    $errors[] = 'Mailchimp: ' . $result['error'];
                } else {
                    Settings::set('mailchimp_api_key',       $apiKey);
                    Settings::set('mailchimp_dc',            $result['dc']);
                    Settings::set('mailchimp_account_name',  $result['account_name']);
                    Settings::set('mailchimp_account_email', $result['email']);
                    $success[] = 'mailchimp';
                }
            }
        }

        if ($section === 'mailchimp_disconnect') {
            Settings::set('mailchimp_api_key',       '');
            Settings::set('mailchimp_dc',            '');
            Settings::set('mailchimp_account_name',  '');
            Settings::set('mailchimp_account_email', '');
            $success[] = 'mailchimp_disconnect';
        }
    }
}

function mailchimpVerifyKey(string $apiKey): array {
    if (!preg_match('/-([a-z0-9]+)$/', $apiKey, $m)) {
        return ['success' => false, 'error' => 'Invalid key format — it should end with a datacenter code (e.g. -us1).'];
    }
    $dc  = $m[1];
    $url = "https://{$dc}.api.mailchimp.com/3.0/";

    if (!function_exists('curl_init')) {
        return ['success' => false, 'error' => 'cURL is not available on this server.'];
    }

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Authorization: Basic ' . base64_encode('anystring:' . $apiKey)],
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    $response  = curl_exec($ch);
    $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        return ['success' => false, 'error' => 'Connection failed: ' . $curlError];
    }
    if ($httpCode === 401) {
        return ['success' => false, 'error' => 'Invalid API key — please check it and try again.'];
    }
    if ($httpCode !== 200) {
        return ['success' => false, 'error' => 'Mailchimp returned an unexpected response (HTTP ' . $httpCode . ').'];
    }

    $data = json_decode($response, true);
    return [
        'success'      => true,
        'dc'           => $dc,
        'account_name' => $data['account_name'] ?? '',
        'email'        => $data['email']        ?? '',
    ];
}

$gaId                = Settings::get('ga_id',                  '');
$gtmId               = Settings::get('gtm_id',                 '');
$metaPixelId         = Settings::get('meta_pixel_id',          '');
$gscVerification     = Settings::get('gsc_verification',       '');
$mailchimpApiKey     = Settings::get('mailchimp_api_key',      '');
$mailchimpAccountName  = Settings::get('mailchimp_account_name',  '');
$mailchimpAccountEmail = Settings::get('mailchimp_account_email', '');

$csrf = Auth::generateCsrf();

function statusBadge(string $value): string {
    if ($value !== '') {
        return '<span class="inline-flex items-center gap-1.5 text-xs font-medium text-green-700 bg-green-50 border border-green-200 rounded-full px-2.5 py-0.5"><span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>Active</span>';
    }
    return '<span class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-500 bg-gray-100 border border-gray-200 rounded-full px-2.5 py-0.5"><span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>Not configured</span>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Integrations &mdash; SquareStack CMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body class="bg-gray-100">

<div class="flex min-h-screen w-full">

<?php $currentPage = 'integrations'; ?>
    <?php require __DIR__ . '/partials/sidebar.php'; ?>

<!-- Main -->
<div class="flex-1 flex flex-col min-h-screen">

    <!-- Top bar -->
    <header class="bg-white border-b border-gray-200 px-8 py-4 flex items-center justify-between sticky top-0 z-20">
        <div>
            <h1 class="text-xl font-semibold text-gray-800">Integrations</h1>
            <p class="text-sm text-gray-500 mt-0.5">Connect your marketing and analytics tools</p>
        </div>
    </header>

    <main class="flex-1 px-8 py-8 max-w-3xl">

        <?php if (!empty($errors)): ?>
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 rounded-xl px-5 py-4 text-sm flex items-start gap-3">
                <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
                <div><?= htmlspecialchars($errors[0]) ?></div>
            </div>
        <?php endif; ?>

        <!-- ===== Google Analytics 4 ===== -->
        <form method="post" action="<?= SITE_URL ?>/admin/integrations.php#ga" class="mb-8">
            <input type="hidden" name="csrf"    value="<?= htmlspecialchars($csrf) ?>">
            <input type="hidden" name="section" value="ga">
            <fieldset class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <legend class="sr-only">Google Analytics 4</legend>
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-orange-50 flex items-center justify-center flex-shrink-0">
                            <i class="fa-brands fa-google text-orange-500 text-sm"></i>
                        </div>
                        <div>
                            <span class="font-semibold text-gray-800 block leading-tight">Google Analytics 4</span>
                            <span class="text-xs text-gray-400">Track visitors, traffic sources, and behaviour</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <?php if (in_array('ga', $success, true)): ?>
                            <span id="ga" class="text-xs text-green-600 font-medium flex items-center gap-1">
                                <i class="fa-solid fa-check"></i> Saved
                            </span>
                        <?php endif; ?>
                        <?= statusBadge($gaId) ?>
                    </div>
                </div>
                <div class="px-6 py-5 space-y-4">
                    <?php if ($gtmId): ?>
                    <div class="flex items-start gap-2.5 bg-amber-50 border border-amber-200 rounded-lg px-4 py-3 text-sm text-amber-800">
                        <i class="fa-solid fa-triangle-exclamation mt-0.5 flex-shrink-0"></i>
                        <span>Google Tag Manager is active. GA4 will be managed through GTM — the ID below is stored but not injected directly to avoid duplicate tracking.</span>
                    </div>
                    <?php endif; ?>
                    <div>
                        <label for="ga_id" class="block text-sm font-medium text-gray-700 mb-1">Measurement ID</label>
                        <input type="text" id="ga_id" name="ga_id"
                               value="<?= htmlspecialchars($gaId) ?>"
                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                               placeholder="G-XXXXXXXXXX">
                        <p class="text-xs text-gray-400 mt-1">Found in your GA4 property under Admin &rarr; Data Streams. Starts with <code class="bg-gray-100 px-1 rounded">G-</code>.</p>
                    </div>
                </div>
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end">
                    <button type="submit"
                            class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                        <i class="fa-solid fa-floppy-disk"></i> Save
                    </button>
                </div>
            </fieldset>
        </form>

        <!-- ===== Google Tag Manager ===== -->
        <form method="post" action="<?= SITE_URL ?>/admin/integrations.php#gtm" class="mb-8">
            <input type="hidden" name="csrf"    value="<?= htmlspecialchars($csrf) ?>">
            <input type="hidden" name="section" value="gtm">
            <fieldset class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <legend class="sr-only">Google Tag Manager</legend>
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid fa-tag text-blue-500 text-sm"></i>
                        </div>
                        <div>
                            <span class="font-semibold text-gray-800 block leading-tight">Google Tag Manager</span>
                            <span class="text-xs text-gray-400">Manage all your tracking tags from one place</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <?php if (in_array('gtm', $success, true)): ?>
                            <span id="gtm" class="text-xs text-green-600 font-medium flex items-center gap-1">
                                <i class="fa-solid fa-check"></i> Saved
                            </span>
                        <?php endif; ?>
                        <?= statusBadge($gtmId) ?>
                    </div>
                </div>
                <div class="px-6 py-5 space-y-4">
                    <p class="text-sm text-gray-500">When GTM is active, it manages all your tags including GA4. Add your GA4 tag inside the GTM container instead of the field above to avoid double-firing.</p>
                    <div>
                        <label for="gtm_id" class="block text-sm font-medium text-gray-700 mb-1">Container ID</label>
                        <input type="text" id="gtm_id" name="gtm_id"
                               value="<?= htmlspecialchars($gtmId) ?>"
                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                               placeholder="GTM-XXXXXXX">
                        <p class="text-xs text-gray-400 mt-1">Found in your GTM workspace. Starts with <code class="bg-gray-100 px-1 rounded">GTM-</code>.</p>
                    </div>
                </div>
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end">
                    <button type="submit"
                            class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                        <i class="fa-solid fa-floppy-disk"></i> Save
                    </button>
                </div>
            </fieldset>
        </form>

        <!-- ===== Meta Pixel ===== -->
        <form method="post" action="<?= SITE_URL ?>/admin/integrations.php#meta_pixel" class="mb-8">
            <input type="hidden" name="csrf"    value="<?= htmlspecialchars($csrf) ?>">
            <input type="hidden" name="section" value="meta_pixel">
            <fieldset class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <legend class="sr-only">Meta Pixel</legend>
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0">
                            <i class="fa-brands fa-meta text-blue-600 text-sm"></i>
                        </div>
                        <div>
                            <span class="font-semibold text-gray-800 block leading-tight">Meta Pixel</span>
                            <span class="text-xs text-gray-400">Track conversions from Facebook &amp; Instagram ads</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <?php if (in_array('meta_pixel', $success, true)): ?>
                            <span id="meta_pixel" class="text-xs text-green-600 font-medium flex items-center gap-1">
                                <i class="fa-solid fa-check"></i> Saved
                            </span>
                        <?php endif; ?>
                        <?= statusBadge($metaPixelId) ?>
                    </div>
                </div>
                <div class="px-6 py-5">
                    <div>
                        <label for="meta_pixel_id" class="block text-sm font-medium text-gray-700 mb-1">Pixel ID</label>
                        <input type="text" id="meta_pixel_id" name="meta_pixel_id"
                               value="<?= htmlspecialchars($metaPixelId) ?>"
                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                               placeholder="1234567890123456">
                        <p class="text-xs text-gray-400 mt-1">Found in Meta Events Manager &rarr; Data Sources. A 15&ndash;16 digit number.</p>
                    </div>
                </div>
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end">
                    <button type="submit"
                            class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                        <i class="fa-solid fa-floppy-disk"></i> Save
                    </button>
                </div>
            </fieldset>
        </form>

        <!-- ===== Google Search Console ===== -->
        <form method="post" action="<?= SITE_URL ?>/admin/integrations.php#gsc" class="mb-8">
            <input type="hidden" name="csrf"    value="<?= htmlspecialchars($csrf) ?>">
            <input type="hidden" name="section" value="gsc">
            <fieldset class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <legend class="sr-only">Google Search Console</legend>
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-green-50 flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid fa-magnifying-glass text-green-600 text-sm"></i>
                        </div>
                        <div>
                            <span class="font-semibold text-gray-800 block leading-tight">Google Search Console</span>
                            <span class="text-xs text-gray-400">Verify site ownership for search performance data</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <?php if (in_array('gsc', $success, true)): ?>
                            <span id="gsc" class="text-xs text-green-600 font-medium flex items-center gap-1">
                                <i class="fa-solid fa-check"></i> Saved
                            </span>
                        <?php endif; ?>
                        <?= statusBadge($gscVerification) ?>
                    </div>
                </div>
                <div class="px-6 py-5 space-y-4">
                    <p class="text-sm text-gray-500">Paste only the <strong>content</strong> value from the verification meta tag. For example, if Google gives you <code class="bg-gray-100 px-1 rounded text-xs">&lt;meta name="google-site-verification" content="abc123..."&gt;</code>, paste just <code class="bg-gray-100 px-1 rounded text-xs">abc123...</code>.</p>
                    <div>
                        <label for="gsc_verification" class="block text-sm font-medium text-gray-700 mb-1">Verification Code</label>
                        <input type="text" id="gsc_verification" name="gsc_verification"
                               value="<?= htmlspecialchars($gscVerification) ?>"
                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                               placeholder="xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx">
                        <p class="text-xs text-gray-400 mt-1">In Search Console: Settings &rarr; Ownership verification &rarr; HTML tag.</p>
                    </div>
                </div>
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end">
                    <button type="submit"
                            class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                        <i class="fa-solid fa-floppy-disk"></i> Save
                    </button>
                </div>
            </fieldset>
        </form>

        <!-- ===== Mailchimp ===== -->
        <?php if (in_array('mailchimp_disconnect', $success, true)): ?>
        <div class="mb-6 bg-green-50 border border-green-200 text-green-700 rounded-xl px-5 py-4 text-sm flex items-center gap-3">
            <i class="fa-solid fa-check-circle"></i> Mailchimp disconnected successfully.
        </div>
        <?php endif; ?>

        <?php if ($mailchimpApiKey && !in_array('mailchimp_disconnect', $success, true)): ?>
        <!-- Connected state -->
        <div class="mb-4 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-yellow-50 flex items-center justify-center flex-shrink-0">
                        <i class="fa-solid fa-envelope-open-text text-yellow-500 text-sm"></i>
                    </div>
                    <div>
                        <span class="font-semibold text-gray-800 block leading-tight">Mailchimp</span>
                        <span class="text-xs text-gray-400">Email marketing &amp; audience management</span>
                    </div>
                </div>
                <span class="inline-flex items-center gap-1.5 text-xs font-medium text-green-700 bg-green-50 border border-green-200 rounded-full px-2.5 py-0.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>Connected
                </span>
            </div>
            <div class="px-6 py-5">
                <div class="flex items-center gap-3 bg-green-50 border border-green-200 rounded-lg px-4 py-3">
                    <i class="fa-solid fa-circle-check text-green-600 flex-shrink-0"></i>
                    <div>
                        <p class="text-sm font-medium text-green-800"><?= htmlspecialchars($mailchimpAccountName) ?></p>
                        <?php if ($mailchimpAccountEmail): ?>
                        <p class="text-xs text-green-700 mt-0.5"><?= htmlspecialchars($mailchimpAccountEmail) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                <form method="post" action="<?= SITE_URL ?>/admin/integrations.php"
                      onsubmit="return confirm('Disconnect Mailchimp from this site?')">
                    <input type="hidden" name="csrf"    value="<?= htmlspecialchars($csrf) ?>">
                    <input type="hidden" name="section" value="mailchimp_disconnect">
                    <button type="submit"
                            class="inline-flex items-center gap-2 text-red-600 hover:text-red-700 text-sm font-medium transition">
                        <i class="fa-solid fa-link-slash text-xs"></i> Disconnect
                    </button>
                </form>
                <button type="button" onclick="document.getElementById('mailchimpReconnect').classList.toggle('hidden')"
                        class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 font-medium transition">
                    <i class="fa-solid fa-rotate text-xs"></i> Update key
                </button>
            </div>
        </div>

        <div id="mailchimpReconnect" class="hidden mb-8">
        <?php else: ?>
        <div class="mb-8" id="mailchimpReconnect">
        <?php endif; ?>
        <form method="post" action="<?= SITE_URL ?>/admin/integrations.php#mailchimp">
            <input type="hidden" name="csrf"    value="<?= htmlspecialchars($csrf) ?>">
            <input type="hidden" name="section" value="mailchimp">
            <fieldset class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <legend class="sr-only">Mailchimp</legend>
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-yellow-50 flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid fa-envelope-open-text text-yellow-500 text-sm"></i>
                        </div>
                        <div>
                            <span class="font-semibold text-gray-800 block leading-tight">Mailchimp</span>
                            <span class="text-xs text-gray-400">Email marketing &amp; audience management</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <?php if (in_array('mailchimp', $success, true)): ?>
                            <span id="mailchimp" class="text-xs text-green-600 font-medium flex items-center gap-1">
                                <i class="fa-solid fa-check"></i> Connected
                            </span>
                        <?php endif; ?>
                        <?= statusBadge($mailchimpApiKey) ?>
                    </div>
                </div>
                <div class="px-6 py-5 space-y-4">
                    <p class="text-sm text-gray-500">The API key is verified before saving. Once connected, forms on this site can subscribe visitors to your Mailchimp audiences.</p>
                    <div>
                        <label for="mailchimp_api_key" class="block text-sm font-medium text-gray-700 mb-1">API Key</label>
                        <div class="relative">
                            <input type="password" id="mailchimp_api_key" name="mailchimp_api_key"
                                   value="<?= htmlspecialchars($mailchimpApiKey) ?>"
                                   class="w-full px-3 py-2 pr-10 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 font-mono"
                                   placeholder="xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx-us1"
                                   autocomplete="off">
                            <button type="button" onclick="toggleApiKeyVisibility()"
                                    class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition">
                                <i id="toggleApiKeyIcon" class="fa-solid fa-eye text-sm"></i>
                            </button>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">Found in Mailchimp under Account &rarr; Extras &rarr; API keys. Ends with your datacenter (e.g. <code class="bg-gray-100 px-1 rounded">-us1</code>).</p>
                    </div>
                </div>
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end">
                    <button type="submit"
                            class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                        <i class="fa-solid fa-plug"></i> Verify &amp; Connect
                    </button>
                </div>
            </fieldset>
        </form>
        </div>

    </main>
</div>

<script>
function toggleApiKeyVisibility() {
    var input = document.getElementById('mailchimp_api_key');
    var icon  = document.getElementById('toggleApiKeyIcon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}
</script>

</div><!-- /.flex.min-h-screen -->
</body>
</html>
