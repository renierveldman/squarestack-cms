<?php
$siteName    = get_site('site_name');
$siteTagline = get_site('site_tagline');
$footerText  = get_site('footer_text');
$footerMenu  = get_menu('footer');
?>
<footer class="bg-gray-900 text-gray-300 mt-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-8">
            <div class="space-y-2">
                <?php if ($siteName): ?>
                <p class="text-white font-semibold text-lg"><?= esc_html($siteName) ?></p>
                <?php endif; ?>
                <?php if ($siteTagline): ?>
                <p class="text-gray-400 text-sm"><?= esc_html($siteTagline) ?></p>
                <?php endif; ?>
            </div>

            <?php if (!empty($footerMenu['items'])): ?>
            <nav aria-label="Footer navigation">
                <?php render_menu('footer', 'flex flex-wrap gap-4', '', 'text-gray-400 hover:text-white text-sm transition-colors'); ?>
            </nav>
            <?php endif; ?>
        </div>

        <div class="mt-8 pt-8 border-t border-gray-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <?php if ($footerText): ?>
            <div class="text-gray-400 text-sm"><?= esc_html($footerText) ?></div>
            <?php endif; ?>
            <p class="text-gray-500 text-sm">&copy; <?= date('Y') ?> <?= esc_html($siteName) ?>. All rights reserved.</p>
        </div>
    </div>
</footer>

</body>
</html>
