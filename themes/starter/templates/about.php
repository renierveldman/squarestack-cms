<?php include THEME_PATH . '/templates/header.php'; ?>

<!-- ═══ PAGE HERO ═════════════════════════════════════════════════════════ -->
<section class="bg-brand-black text-white py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <p class="text-xs uppercase tracking-[0.25em] text-gray-500 mb-5">— About Us</p>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-end">
            <h1 class="text-5xl lg:text-6xl font-black leading-tight tracking-tight">
                <?= esc_html(get_field('page_headline') ?: "We're a small team that does serious work.") ?>
            </h1>
            <p class="text-gray-400 text-lg leading-relaxed">
                <?= esc_html(get_field('page_intro') ?: 'Built to be a true partner — not just another agency. We take a limited number of clients so every business we work with gets our full attention.') ?>
            </p>
        </div>
    </div>
</section>

<!-- ═══ STORY (white) ════════════════════════════════════════════════════ -->
<section class="bg-white py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">

            <!-- Image -->
            <div>
                <?php $storyImage = get_field('story_image'); ?>
                <?php if ($storyImage): ?>
                <img src="<?= esc_url($storyImage) ?>" alt="" class="w-full h-[460px] object-cover rounded-2xl">
                <?php else: ?>
                <div class="w-full h-[460px] rounded-2xl bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
                    <p class="text-gray-400 text-sm">Story image</p>
                </div>
                <?php endif; ?>
            </div>

            <!-- Copy -->
            <div>
                <p class="text-xs uppercase tracking-[0.25em] text-gray-400 mb-5">— Our Story</p>
                <h2 class="text-4xl font-black tracking-tight mb-6">
                    <?= esc_html(get_field('story_headline') ?: 'Built out of frustration with how agencies work.') ?>
                </h2>
                <div class="space-y-4 text-gray-600 leading-relaxed">
                    <p><?= esc_html(get_field('story_body_1') ?: 'We started because we kept seeing the same pattern — businesses getting beautiful deliverables but no real results. Great-looking websites with no traffic. Ad accounts burning budget with no strategy. Reports that looked impressive but told you nothing useful.') ?></p>
                    <p><?= esc_html(get_field('story_body_2') ?: 'So we built something different. A team that takes full ownership of your digital operation — from strategy to execution to reporting — and stays accountable to the outcomes, not just the outputs.') ?></p>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- ═══ VALUES (dark) ════════════════════════════════════════════════════ -->
<section class="bg-brand-black text-white py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="text-center mb-16">
            <p class="text-xs uppercase tracking-[0.25em] text-gray-500 mb-4">— How We Work</p>
            <h2 class="text-4xl font-black tracking-tight">What you can always expect from us.</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php
            $values = [
                ['fa-magnifying-glass', 'Radical Transparency',  'You always know what we\'re doing, why we\'re doing it, and what it\'s producing. No smoke and mirrors.'],
                ['fa-handshake',        'Partnership First',      'We work inside your business, not at arm\'s length. We care about your results as much as you do.'],
                ['fa-clock-rotate-left','Long-Term Thinking',     'We don\'t optimise for quick wins that fall apart. We build systems and strategies that compound over time.'],
                ['fa-gauge-high',       'Execution Over Decks',   'Less presenting, more doing. You\'ll see action and results before you see a fancy presentation.'],
                ['fa-circle-nodes',     'Connected Thinking',     'Marketing, technology, and strategy working together — not in silos. Everything we do is designed to reinforce everything else.'],
                ['fa-shield-halved',    'Accountability',         'We own the outcomes. If something isn\'t working, we tell you first and fix it — not the other way around.'],
            ];
            foreach ($values as [$icon, $title, $desc]):
            ?>
            <div class="p-7 bg-white/5 border border-white/10 rounded-2xl hover:bg-white/8 transition-colors">
                <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center mb-5">
                    <i class="fa-solid <?= $icon ?> text-white text-sm"></i>
                </div>
                <h3 class="font-bold text-white mb-2"><?= esc_html($title) ?></h3>
                <p class="text-sm text-gray-500 leading-relaxed"><?= esc_html($desc) ?></p>
            </div>
            <?php endforeach; ?>
        </div>

    </div>
</section>

<!-- ═══ CTA ═══════════════════════════════════════════════════════════════ -->
<section class="bg-white py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <p class="text-xs uppercase tracking-[0.25em] text-gray-400 mb-5">— Work With Us</p>
        <h2 class="text-4xl sm:text-5xl font-black tracking-tight mb-6">
            Ready to work with a team<br>that actually shows up?
        </h2>
        <p class="text-gray-500 mb-10 max-w-md mx-auto leading-relaxed">
            Let's have an honest conversation about where your business is and where you want it to go.
        </p>
        <a href="<?= esc_url(site_url('/contact')) ?>"
           class="inline-flex items-center gap-2 bg-black text-white font-bold px-8 py-4 rounded-xl hover:bg-gray-800 transition text-sm">
            Book a Discovery Call <span>&rarr;</span>
        </a>
    </div>
</section>

<?php include THEME_PATH . '/templates/footer.php'; ?>
