<?php
include THEME_PATH . '/templates/header.php';

$heroImage   = get_field('hero_image');
$blogSlug    = get_site('blog_slug') ?: 'blog';
$latestPosts = get_posts(['limit' => 3, 'status' => 'published']);

$phone   = get_theme_option('phone');
$email   = get_theme_option('email');
?>

<!-- ═══ HERO ═══════════════════════════════════════════════════════════════ -->
<section class="bg-brand-black text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 lg:py-32">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">

            <!-- Left: copy -->
            <div>
                <p class="text-xs uppercase tracking-[0.25em] text-gray-400 mb-8">
                    — <?= esc_html(get_site('site_tagline') ?: 'Your Digital Partner') ?>
                </p>
                <h1 class="text-5xl sm:text-6xl lg:text-7xl font-black leading-[1.05] tracking-tight mb-8">
                    <?php
                    $heroTitle = get_field('hero_title') ?: get_the_title();
                    $lines = explode("\n", $heroTitle);
                    $last  = count($lines) - 1;
                    foreach ($lines as $i => $line):
                        $line = trim($line);
                        if (!$line) continue;
                    ?>
                    <?php if ($i === $last): ?>
                    <span class="text-white"><?= esc_html($line) ?></span>
                    <?php else: ?>
                    <?= esc_html($line) ?><br>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </h1>
                <p class="text-gray-300 text-lg leading-relaxed max-w-md mb-10">
                    <?= esc_html(get_field('hero_subtitle') ?: 'We help businesses grow online with strategy, design, and technology that works together — not separately.') ?>
                </p>
                <div class="flex flex-wrap gap-4">
                    <?php $ctaText = get_field('cta_text'); $ctaUrl = get_field('cta_url'); ?>
                    <a href="<?= esc_url($ctaUrl ?: site_url('/services')) ?>"
                       class="inline-flex items-center gap-2 bg-white text-black font-semibold px-6 py-3 rounded-lg hover:bg-gray-100 transition text-sm">
                        <?= esc_html($ctaText ?: 'See What We Do') ?> <span>&rarr;</span>
                    </a>
                    <a href="<?= esc_url(site_url('/contact')) ?>"
                       class="inline-flex items-center gap-2 border border-white/30 text-white font-semibold px-6 py-3 rounded-lg hover:border-white/60 hover:bg-white/5 transition text-sm">
                        Book a Discovery Call <span>&rarr;</span>
                    </a>
                </div>
            </div>

            <!-- Right: image or placeholder -->
            <div class="relative">
                <?php if ($heroImage): ?>
                <img src="<?= esc_url($heroImage) ?>" alt=""
                     class="w-full h-[480px] object-cover rounded-2xl">
                <?php else: ?>
                <div class="w-full h-[480px] rounded-2xl bg-gradient-to-br from-gray-800 to-gray-900 flex items-center justify-center border border-white/10">
                    <p class="text-gray-600 text-sm">Hero image</p>
                </div>
                <?php endif; ?>
                <!-- Floating stat -->
                <div class="absolute bottom-6 left-6 bg-white text-black px-5 py-3 rounded-xl shadow-lg">
                    <p class="text-2xl font-black leading-none"><?= esc_html(get_field('stat_number') ?: '100%') ?></p>
                    <p class="text-xs font-semibold mt-0.5"><?= esc_html(get_field('stat_label') ?: 'Client satisfaction') ?></p>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- ═══ SERVICES / DIVISIONS ══════════════════════════════════════════════ -->
<section class="bg-white py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-end mb-16">
            <div>
                <p class="text-xs uppercase tracking-[0.25em] text-gray-400 mb-4">— What We Do</p>
                <h2 class="text-4xl lg:text-5xl font-black leading-tight tracking-tight">
                    One team.<br>Every solution<br>your business needs.
                </h2>
            </div>
            <div>
                <p class="text-gray-500 leading-relaxed">
                    Every business is at a different stage. We meet you where you are and build from there — with the right mix of services to drive real growth.
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <?php
            $services = [
                [
                    'icon'  => 'fa-bullseye',
                    'title' => get_field('div_1_title') ?: 'Marketing & Growth',
                    'desc'  => get_field('div_1_desc')  ?: 'Paid ads, SEO, content strategy, and campaign management that fills your pipeline and keeps it full.',
                    'link'  => site_url('/services'),
                ],
                [
                    'icon'  => 'fa-laptop-code',
                    'title' => get_field('div_2_title') ?: 'Web & Technology',
                    'desc'  => get_field('div_2_desc')  ?: 'Websites, platforms, and integrations built to perform — fast, secure, and easy to manage.',
                    'link'  => site_url('/services'),
                ],
                [
                    'icon'  => 'fa-chart-mixed',
                    'title' => get_field('div_3_title') ?: 'Strategy & Consulting',
                    'desc'  => get_field('div_3_desc')  ?: 'Clear thinking, honest reporting, and strategic direction so you always know what\'s working and what\'s next.',
                    'link'  => site_url('/services'),
                ],
            ];
            foreach ($services as $svc):
            ?>
            <div class="group bg-gray-50 hover:bg-black border border-gray-200 hover:border-black rounded-2xl p-8 flex flex-col transition-colors duration-300">
                <div class="w-10 h-10 rounded-xl bg-white/5 group-hover:bg-white/10 flex items-center justify-center mb-6 transition-colors">
                    <i class="fa-solid <?= $svc['icon'] ?> text-white text-sm"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 group-hover:text-white mb-3 transition-colors"><?= esc_html($svc['title']) ?></h3>
                <p class="text-gray-500 group-hover:text-gray-400 text-sm leading-relaxed flex-grow transition-colors"><?= esc_html($svc['desc']) ?></p>
                <a href="<?= esc_url($svc['link']) ?>"
                   class="inline-flex items-center gap-1.5 mt-6 text-sm font-semibold text-black group-hover:text-white transition-colors">
                    Explore <span>&rarr;</span>
                </a>
            </div>
            <?php endforeach; ?>
        </div>

    </div>
</section>

<!-- ═══ WHY US (dark) ═════════════════════════════════════════════════════ -->
<section class="bg-brand-black text-white py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">

            <!-- Left: two stacked image boxes -->
            <div class="relative h-[460px] hidden lg:block">
                <?php $whyImage = get_field('why_image'); ?>
                <?php if ($whyImage): ?>
                    <img src="<?= esc_url($whyImage) ?>" alt=""
                         class="absolute inset-0 w-full h-full object-cover rounded-2xl">
                <?php else: ?>
                    <div class="absolute top-0 left-0 w-4/5 h-72 rounded-2xl bg-gradient-to-br from-gray-700 to-gray-800 border border-white/10"></div>
                    <div class="absolute bottom-0 right-0 w-3/5 h-52 rounded-2xl bg-gradient-to-br from-gray-800 to-gray-900 border border-white/10"></div>
                <?php endif; ?>
                <!-- Floating badge -->
                <div class="absolute bottom-8 left-8 bg-white text-black rounded-xl px-5 py-3 shadow-xl z-10">
                    <p class="text-3xl font-black leading-none"><?= esc_html(get_field('proof_number') ?: 'Zero') ?></p>
                    <p class="text-xs font-semibold mt-1"><?= esc_html(get_field('proof_label') ?: 'Clients left behind') ?></p>
                </div>
            </div>

            <!-- Right: copy -->
            <div>
                <p class="text-xs uppercase tracking-[0.25em] text-gray-500 mb-6">— Why Us</p>
                <h2 class="text-4xl lg:text-5xl font-black leading-tight tracking-tight mb-8">
                    <?= esc_html(get_field('why_headline') ?: 'We own the outcome, not just the deliverable.') ?>
                </h2>
                <p class="text-gray-400 leading-relaxed mb-5">
                    <?= esc_html(get_field('why_body_1') ?: 'Most agencies hand over a finished asset and move on. We embed into your business, take full ownership of your digital operation, and stay accountable to what it produces — not just what we build.') ?>
                </p>
                <p class="text-gray-400 leading-relaxed mb-8">
                    <?= esc_html(get_field('why_body_2') ?: 'A small, focused team that works with a deliberately limited number of clients, so every business we partner with gets the attention, strategy, and expertise it deserves.') ?>
                </p>
                <div class="flex items-start gap-3 mb-10 p-4 bg-white/5 rounded-xl border border-white/10">
                    <i class="fa-solid fa-circle-check text-white mt-0.5 flex-shrink-0"></i>
                    <p class="text-sm text-gray-300"><?= esc_html(get_field('why_point') ?: 'Every client gets a dedicated team member, clear reporting, and direct access — no account managers, no handoffs.') ?></p>
                </div>
                <a href="<?= esc_url(site_url('/about')) ?>"
                   class="inline-flex items-center gap-2 bg-white text-black font-semibold px-6 py-3 rounded-lg hover:bg-gray-100 transition text-sm">
                    About Us <span>&rarr;</span>
                </a>
            </div>

        </div>
    </div>
</section>

<!-- ═══ NUMBERS (white) ═══════════════════════════════════════════════════ -->
<section class="bg-white py-24 border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="text-center mb-16">
            <p class="text-xs uppercase tracking-[0.25em] text-gray-400 mb-4">— The Numbers</p>
            <h2 class="text-4xl lg:text-5xl font-black tracking-tight">
                What working with us actually delivers.
            </h2>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-8">
            <?php
            $stats = [
                [get_field('stat_1_num') ?: '10+',   get_field('stat_1_label') ?: 'Long-term clients'],
                [get_field('stat_2_num') ?: '5+',    get_field('stat_2_label') ?: 'Years of delivery'],
                [get_field('stat_3_num') ?: '100%',  get_field('stat_3_label') ?: 'Client retention'],
                [get_field('stat_4_num') ?: '30d',   get_field('stat_4_label') ?: 'Average onboarding'],
            ];
            foreach ($stats as [$num, $label]):
            ?>
            <div class="text-center py-8 border-t-2 border-black">
                <p class="text-5xl font-black text-brand-black leading-none mb-3"><?= esc_html($num) ?></p>
                <p class="text-sm text-gray-500"><?= esc_html($label) ?></p>
            </div>
            <?php endforeach; ?>
        </div>

    </div>
</section>

<!-- ═══ LATEST INSIGHTS (dark) ════════════════════════════════════════════ -->
<?php if (!empty($latestPosts)): ?>
<section class="bg-brand-black text-white py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="flex items-end justify-between mb-12">
            <div>
                <p class="text-xs uppercase tracking-[0.25em] text-gray-500 mb-4">— Insights</p>
                <h2 class="text-4xl font-black tracking-tight">Latest from the blog.</h2>
            </div>
            <a href="<?= esc_url(site_url('/' . $blogSlug)) ?>"
               class="hidden sm:inline-flex items-center gap-1.5 text-sm text-gray-400 hover:text-white transition font-medium">
                Read all <span>&rarr;</span>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <?php foreach ($latestPosts as $lp):
                $permalink = site_url('/' . $blogSlug . '/' . ($lp['slug'] ?? ''));
                $excerpt   = mb_strimwidth(strip_tags($lp['excerpt'] ?? $lp['content'] ?? ''), 0, 110, '…');
            ?>
            <a href="<?= esc_url($permalink) ?>" class="group block bg-white/5 hover:bg-white/10 border border-white/10 hover:border-white/20 rounded-2xl overflow-hidden transition-colors">
                <?php if (!empty($lp['featured_image'])): ?>
                <div class="overflow-hidden h-48">
                    <img src="<?= esc_url($lp['featured_image']) ?>" alt="<?= esc_attr($lp['title'] ?? '') ?>"
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                </div>
                <?php endif; ?>
                <div class="p-6">
                    <?php if (!empty($lp['published_at'] ?? $lp['created_at'])): ?>
                    <p class="text-xs text-gray-500 mb-3"><?= date('d M Y', strtotime($lp['published_at'] ?? $lp['created_at'])) ?></p>
                    <?php endif; ?>
                    <h3 class="font-bold text-white group-hover:text-white transition-colors mb-2 leading-snug"><?= esc_html($lp['title'] ?? '') ?></h3>
                    <p class="text-sm text-gray-500 leading-relaxed"><?= esc_html($excerpt) ?></p>
                    <p class="mt-4 text-xs text-white font-semibold">Read more &rarr;</p>
                </div>
            </a>
            <?php endforeach; ?>
        </div>

    </div>
</section>
<?php endif; ?>

<!-- ═══ BOTTOM CTA ════════════════════════════════════════════════════════ -->
<section class="bg-white py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-brand-black rounded-3xl px-8 sm:px-16 py-16 text-white text-center">
            <p class="text-xs uppercase tracking-[0.25em] text-gray-500 mb-5">— Ready to Start</p>
            <h2 class="text-4xl sm:text-5xl font-black tracking-tight mb-6 max-w-2xl mx-auto">
                <?= esc_html(get_field('cta_headline') ?: "Let's build something that actually works.") ?>
            </h2>
            <p class="text-gray-400 mb-10 max-w-lg mx-auto leading-relaxed">
                <?= esc_html(get_field('cta_subtext') ?: 'Book a free 30-minute discovery call. We\'ll review your current setup and tell you what\'s working, what isn\'t, and what we\'d change.') ?>
            </p>
            <div class="flex flex-wrap gap-4 justify-center">
                <a href="<?= esc_url(site_url('/contact')) ?>"
                   class="inline-flex items-center gap-2 bg-white text-black font-bold px-7 py-3.5 rounded-xl hover:bg-gray-100 transition text-sm">
                    Book a Discovery Call <span>&rarr;</span>
                </a>
                <a href="<?= esc_url(site_url('/services')) ?>"
                   class="inline-flex items-center gap-2 border border-white/20 text-white font-semibold px-7 py-3.5 rounded-xl hover:border-white/40 hover:bg-white/5 transition text-sm">
                    View Our Services <span>&rarr;</span>
                </a>
            </div>
        </div>
    </div>
</section>

<?php include THEME_PATH . '/templates/footer.php'; ?>
