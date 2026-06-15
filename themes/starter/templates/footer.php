<?php
$siteName    = get_site('site_name');
$siteTagline = get_site('site_tagline');
$footerText  = get_site('footer_text');
$footerMenu  = get_menu('footer');

$address         = get_theme_option('address');
$phone           = get_theme_option('phone');
$email           = get_theme_option('email');
$socialFacebook  = get_theme_option('social_facebook');
$socialInstagram = get_theme_option('social_instagram');
$socialLinkedin  = get_theme_option('social_linkedin');
$socialTwitter   = get_theme_option('social_twitter');

$socials = array_filter([
    ['url' => $socialFacebook,  'icon' => 'fa-facebook',  'label' => 'Facebook'],
    ['url' => $socialInstagram, 'icon' => 'fa-instagram', 'label' => 'Instagram'],
    ['url' => $socialLinkedin,  'icon' => 'fa-linkedin',  'label' => 'LinkedIn'],
    ['url' => $socialTwitter,   'icon' => 'fa-x-twitter', 'label' => 'X'],
], fn($s) => !empty($s['url']));
?>
<footer class="bg-gray-900 text-gray-300 mt-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-10">

            <!-- Brand + contact -->
            <div class="space-y-3 max-w-xs">
                <?php if ($siteName): ?>
                <p class="text-white font-semibold text-lg"><?= esc_html($siteName) ?></p>
                <?php endif; ?>
                <?php if ($siteTagline): ?>
                <p class="text-gray-400 text-sm"><?= esc_html($siteTagline) ?></p>
                <?php endif; ?>

                <?php if ($address): ?>
                <p class="text-gray-400 text-sm leading-relaxed whitespace-pre-line">
                    <i class="fa-solid fa-location-dot mr-1.5 text-gray-500"></i><?= esc_html($address) ?>
                </p>
                <?php endif; ?>

                <?php if ($phone): ?>
                <p class="text-gray-400 text-sm">
                    <i class="fa-solid fa-phone mr-1.5 text-gray-500"></i>
                    <a href="tel:<?= esc_attr(preg_replace('/[^+\d]/', '', $phone)) ?>" class="hover:text-white transition-colors"><?= esc_html($phone) ?></a>
                </p>
                <?php endif; ?>

                <?php if ($email): ?>
                <p class="text-gray-400 text-sm">
                    <i class="fa-solid fa-envelope mr-1.5 text-gray-500"></i>
                    <a href="mailto:<?= esc_attr($email) ?>" class="hover:text-white transition-colors"><?= esc_html($email) ?></a>
                </p>
                <?php endif; ?>
            </div>

            <!-- Footer nav -->
            <?php if (!empty($footerMenu['items'])): ?>
            <nav aria-label="Footer navigation">
                <p class="text-white text-sm font-semibold mb-3">Navigation</p>
                <?php render_menu('footer', 'flex flex-col gap-2', '', 'text-gray-400 hover:text-white text-sm transition-colors'); ?>
            </nav>
            <?php endif; ?>

            <!-- Social icons -->
            <?php if (!empty($socials)): ?>
            <div>
                <p class="text-white text-sm font-semibold mb-3">Follow Us</p>
                <div class="flex items-center gap-3">
                    <?php foreach ($socials as $social): ?>
                    <a href="<?= esc_url($social['url']) ?>"
                       target="_blank" rel="noopener noreferrer"
                       aria-label="<?= esc_attr($social['label']) ?>"
                       class="w-9 h-9 rounded-full bg-gray-800 hover:bg-indigo-600 flex items-center justify-center text-gray-400 hover:text-white transition-colors">
                        <i class="fa-brands <?= esc_attr($social['icon']) ?> text-sm"></i>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
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
