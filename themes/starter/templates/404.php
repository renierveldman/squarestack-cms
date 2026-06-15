<?php http_response_code(404); ?>
<?php include THEME_PATH . '/templates/header.php'; ?>

<main class="flex flex-col items-center justify-center text-center py-32 px-4">
    <div class="text-9xl font-black text-indigo-100 select-none leading-none">404</div>
    <h1 class="mt-4 text-3xl font-bold text-gray-900 tracking-tight">Page not found</h1>
    <p class="mt-4 text-gray-500 max-w-sm">Sorry, the page you are looking for does not exist or has been moved.</p>
    <a href="<?= site_url('/') ?>" class="mt-8 inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-5 py-3 text-sm font-semibold text-white shadow hover:bg-indigo-500 transition-colors">
        &larr; Go Home
    </a>
</main>

<?php include THEME_PATH . '/templates/footer.php'; ?>
