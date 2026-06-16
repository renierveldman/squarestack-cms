<?php
require_once __DIR__ . '/../config.php';
require_once CORE_PATH . '/Database.php';
require_once CORE_PATH . '/Auth.php';
require_once CORE_PATH . '/Settings.php';

// Already logged in — redirect to dashboard
if (Auth::check() !== false) {
    header('Location: ' . SITE_URL . '/admin/index.php');
    exit;
}

$error     = '';
$lockedOut = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token    = $_POST['csrf_token'] ?? '';
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!Auth::verifyCsrf($token)) {
        $error = 'Invalid or expired request. Please try again.';
    } elseif ($email === '' || $password === '') {
        $error = 'Please enter your email and password.';
    } elseif (Auth::isLockedOut($email)) {
        $lockedOut = true;
        $secs      = Auth::lockoutSecondsRemaining($email);
        $mins      = (int) ceil($secs / 60);
        $error     = "Too many failed attempts. Please wait {$mins} minute" . ($mins === 1 ? '' : 's') . ' before trying again.';
    } else {
        if (Auth::login($email, $password)) {
            setcookie(
                'squarestack_admin',
                '1',
                [
                    'expires'  => 0,
                    'path'     => '/',
                    'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
                    'httponly' => true,
                    'samesite' => 'Strict',
                ]
            );
            header('Location: ' . SITE_URL . '/admin/index.php');
            exit;
        } else {
            // Login failed — check if this attempt triggered a lockout
            if (Auth::isLockedOut($email)) {
                $lockedOut = true;
                $secs      = Auth::lockoutSecondsRemaining($email);
                $mins      = (int) ceil($secs / 60);
                $error     = "Too many failed attempts. Your account is locked for {$mins} minute" . ($mins === 1 ? '' : 's') . '.';
            } else {
                $error = 'Invalid email or password.';
            }
        }
    }
}

$csrfToken = Auth::generateCsrf();
$siteName  = Settings::get('site_name', 'SquareStack CMS');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — <?php echo htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8'); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex items-center justify-center" style="background-color:#0f172a;">

    <div class="w-full max-w-md px-4">
        <div class="bg-white rounded-2xl shadow-2xl p-8">

            <!-- Site name -->
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">
                    <?php echo htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8'); ?>
                </h1>
                <p class="mt-1 text-sm text-slate-500">Sign in to your admin panel</p>
            </div>

            <?php if ($error !== ''): ?>
                <div class="mb-5 rounded-lg <?php echo $lockedOut ? 'bg-amber-50 border-amber-200 text-amber-800' : 'bg-red-50 border-red-200 text-red-700'; ?> border px-4 py-3 text-sm">
                    <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">

                <div class="mb-5">
                    <label for="email" class="block text-sm font-medium text-slate-700 mb-1.5">
                        Email address
                    </label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="<?php echo htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                        required
                        autofocus
                        autocomplete="email"
                        <?php echo $lockedOut ? 'disabled' : ''; ?>
                        class="block w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-slate-900 text-sm placeholder-slate-400 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 transition <?php echo $lockedOut ? 'opacity-50 cursor-not-allowed' : ''; ?>"
                        placeholder="you@example.com"
                    >
                </div>

                <div class="mb-7">
                    <label for="password" class="block text-sm font-medium text-slate-700 mb-1.5">
                        Password
                    </label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        <?php echo $lockedOut ? 'disabled' : ''; ?>
                        class="block w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-slate-900 text-sm placeholder-slate-400 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 transition <?php echo $lockedOut ? 'opacity-50 cursor-not-allowed' : ''; ?>"
                        placeholder="••••••••"
                    >
                </div>

                <button
                    type="submit"
                    <?php echo $lockedOut ? 'disabled' : ''; ?>
                    class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-indigo-700 transition <?php echo $lockedOut ? 'opacity-50 cursor-not-allowed' : ''; ?>"
                >
                    <?php echo $lockedOut ? 'Account Locked' : 'Sign in'; ?>
                </button>
            </form>

        </div>
    </div>

</body>
</html>
