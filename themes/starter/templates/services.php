<?php include THEME_PATH . '/templates/header.php'; ?>

<!-- ═══ PAGE HERO ═════════════════════════════════════════════════════════ -->
<section class="bg-brand-black text-white py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <p class="text-xs uppercase tracking-[0.25em] text-gray-500 mb-5">— Our Services</p>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-end">
            <h1 class="text-5xl lg:text-6xl font-black leading-tight tracking-tight">
                <?= esc_html(get_field('page_headline') ?: 'Everything your business needs to grow online.') ?>
            </h1>
            <p class="text-gray-400 text-lg leading-relaxed">
                <?= esc_html(get_field('page_intro') ?: 'We don\'t do everything — we do the things that move the needle. Strategy, technology, and marketing working together as one connected system.') ?>
            </p>
        </div>
    </div>
</section>

<!-- ═══ SERVICES GRID (white) ═════════════════════════════════════════════ -->
<section class="bg-white py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="space-y-6">
            <?php
            $services = [
                [
                    'number' => '01',
                    'icon'   => 'fa-bullseye',
                    'title'  => 'Digital Marketing',
                    'desc'   => 'From paid search and social to SEO and email — we build and manage campaigns that drive qualified traffic, generate leads, and grow revenue. Every campaign is tied to a clear business outcome.',
                    'points' => ['Google & Meta Ads', 'Search Engine Optimisation', 'Email Marketing', 'Content Strategy'],
                ],
                [
                    'number' => '02',
                    'icon'   => 'fa-laptop-code',
                    'title'  => 'Web Design & Development',
                    'desc'   => 'Websites that look great, load fast, and convert. We design and build for performance — not just aesthetics — with CMS integration, e-commerce, and everything in between.',
                    'points' => ['Design & UX', 'CMS Development', 'E-commerce', 'Performance Optimisation'],
                ],
                [
                    'number' => '03',
                    'icon'   => 'fa-chart-mixed',
                    'title'  => 'Strategy & Consulting',
                    'desc'   => 'Clear direction for businesses that are ready to scale. We audit what you have, identify what\'s missing, and build a roadmap that connects every part of your digital operation.',
                    'points' => ['Digital Audits', 'Growth Roadmaps', 'Analytics & Reporting', 'Competitor Analysis'],
                ],
                [
                    'number' => '04',
                    'icon'   => 'fa-circle-nodes',
                    'title'  => 'Systems & Integrations',
                    'desc'   => 'CRMs, automation, APIs, and third-party tools that make your business run smarter. We set up the infrastructure that lets your team work faster and your marketing work harder.',
                    'points' => ['CRM Setup & Management', 'Marketing Automation', 'API Integrations', 'Workflow Design'],
                ],
            ];
            foreach ($services as $svc):
            ?>
            <div class="group grid grid-cols-1 lg:grid-cols-[80px_1fr_280px] gap-8 items-start p-8 border border-gray-200 rounded-2xl hover:border-black hover:bg-black hover:text-white transition-colors duration-300">
                <div class="text-4xl font-black text-gray-200 group-hover:text-white/20 transition-colors"><?= $svc['number'] ?></div>
                <div>
                    <div class="flex items-center gap-3 mb-3">
                        <i class="fa-solid <?= $svc['icon'] ?> text-white text-sm"></i>
                        <h3 class="text-xl font-bold"><?= esc_html($svc['title']) ?></h3>
                    </div>
                    <p class="text-gray-500 group-hover:text-gray-400 leading-relaxed text-sm transition-colors"><?= esc_html($svc['desc']) ?></p>
                </div>
                <div class="flex flex-wrap gap-2 lg:justify-end">
                    <?php foreach ($svc['points'] as $pt): ?>
                    <span class="text-xs font-medium px-3 py-1.5 bg-gray-100 group-hover:bg-white/10 text-gray-700 group-hover:text-gray-300 rounded-full transition-colors"><?= esc_html($pt) ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

    </div>
</section>

<!-- ═══ PROCESS (dark) ═══════════════════════════════════════════════════ -->
<section class="bg-brand-black text-white py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="text-center mb-16">
            <p class="text-xs uppercase tracking-[0.25em] text-gray-500 mb-4">— How It Works</p>
            <h2 class="text-4xl font-black tracking-tight">From first conversation to full delivery.</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <?php
            $steps = [
                ['01', 'Discovery Call',    'A focused 30-minute conversation about your business, goals, and current digital setup.'],
                ['02', 'Audit & Proposal',  'We review what you have and come back with a clear, honest assessment and a proposed plan.'],
                ['03', 'Onboarding',        'We set up access, tools, and workflows — typically live within 2 weeks of signing.'],
                ['04', 'Execution',         'Ongoing delivery with monthly reporting, regular check-ins, and continuous optimisation.'],
            ];
            foreach ($steps as [$num, $title, $desc]):
            ?>
            <div class="relative">
                <div class="text-6xl font-black text-white/5 mb-4 leading-none"><?= $num ?></div>
                <div class="h-px bg-white/20 w-8 mb-4"></div>
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
        <p class="text-xs uppercase tracking-[0.25em] text-gray-400 mb-5">— Get Started</p>
        <h2 class="text-4xl sm:text-5xl font-black tracking-tight mb-6">
            Not sure which service<br>you need? Let's figure it out.
        </h2>
        <p class="text-gray-500 mb-10 max-w-md mx-auto leading-relaxed">
            Tell us about your business and we'll tell you exactly what we'd focus on first.
        </p>
        <a href="<?= esc_url(site_url('/contact')) ?>"
           class="inline-flex items-center gap-2 bg-black text-white font-bold px-8 py-4 rounded-xl hover:bg-gray-800 transition text-sm">
            Book a Free Discovery Call <span>&rarr;</span>
        </a>
    </div>
</section>

<?php include THEME_PATH . '/templates/footer.php'; ?>
