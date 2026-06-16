<?php
include THEME_PATH . '/templates/header.php';

$phone   = get_field('contact_phone') ?: get_theme_option('phone');
$email   = get_field('contact_email') ?: get_theme_option('email');
$address = get_theme_option('address');
$intro   = get_field('contact_intro');
?>

<!-- ═══ PAGE HERO ═════════════════════════════════════════════════════════ -->
<section class="bg-brand-black text-white py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <p class="text-xs uppercase tracking-[0.25em] text-gray-500 mb-5">— Contact</p>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-end">
            <h1 class="text-5xl lg:text-6xl font-black leading-tight tracking-tight">
                Let's have an honest conversation.
            </h1>
            <p class="text-gray-400 text-lg leading-relaxed">
                <?= esc_html($intro ?: 'No sales pitch — just a straight conversation about your business and whether we\'re the right fit to help you grow it.') ?>
            </p>
        </div>
    </div>
</section>

<!-- ═══ CONTACT SPLIT (white) ═════════════════════════════════════════════ -->
<section class="bg-white py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-16">

            <!-- Left: info -->
            <div class="lg:col-span-2 space-y-10">

                <div>
                    <h2 class="text-2xl font-black mb-2">Book a Discovery Call</h2>
                    <p class="text-gray-500 text-sm leading-relaxed">A free 30-minute call to understand your goals, review your current setup, and give you honest advice — no strings attached.</p>
                </div>

                <div class="space-y-6">
                    <?php if ($phone): ?>
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i class="fa-solid fa-phone text-gray-600 text-sm"></i>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-widest text-gray-400 mb-1">Phone</p>
                            <a href="tel:<?= esc_attr(preg_replace('/[^+\d]/', '', $phone)) ?>"
                               class="font-semibold text-gray-900 hover:text-black transition"><?= esc_html($phone) ?></a>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($email): ?>
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i class="fa-solid fa-envelope text-gray-600 text-sm"></i>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-widest text-gray-400 mb-1">Email</p>
                            <a href="mailto:<?= esc_attr($email) ?>"
                               class="font-semibold text-gray-900 hover:text-black transition"><?= esc_html($email) ?></a>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($address): ?>
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i class="fa-solid fa-location-dot text-gray-600 text-sm"></i>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-widest text-gray-400 mb-1">Location</p>
                            <p class="font-semibold text-gray-900 whitespace-pre-line"><?= esc_html($address) ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- What to expect -->
                <div class="bg-gray-50 border border-gray-200 rounded-2xl p-6">
                    <p class="text-xs uppercase tracking-widest text-gray-400 mb-4">What to Expect</p>
                    <ul class="space-y-3">
                        <?php foreach ([
                            'A response within 1 business day',
                            'A focused 30-minute call — no fluff',
                            'Honest advice, even if we\'re not the right fit',
                        ] as $pt): ?>
                        <li class="flex items-start gap-2.5 text-sm text-gray-600">
                            <i class="fa-solid fa-check text-gray-900 mt-0.5 flex-shrink-0 text-xs"></i>
                            <?= esc_html($pt) ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

            </div>

            <!-- Right: form -->
            <div class="lg:col-span-3">
                <div class="bg-gray-50 border border-gray-200 rounded-2xl p-8 sm:p-10">
                    <h3 class="text-xl font-bold mb-2">Send us a message</h3>
                    <p class="text-sm text-gray-500 mb-8">We'll get back to you within one business day.</p>
                    <?php render_form('contact'); ?>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- ═══ MAP (if embed provided) ══════════════════════════════════════════ -->
<?php $mapEmbed = get_field('map_embed'); if ($mapEmbed): ?>
<section class="bg-white border-t border-gray-100">
    <div class="h-80 w-full overflow-hidden">
        <?= $mapEmbed ?>
    </div>
</section>
<?php endif; ?>

<?php include THEME_PATH . '/templates/footer.php'; ?>
