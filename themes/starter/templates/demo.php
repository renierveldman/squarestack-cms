<?php include THEME_PATH . '/templates/header.php'; ?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

    <h1 class="text-4xl font-bold text-gray-900"><?= esc_html(get_the_title()) ?></h1>

    <?php $test1 = get_field('test_1'); if ($test1): ?>
    <div class="mt-8 p-6 bg-indigo-50 border border-indigo-200 rounded-xl">
        <p class="text-xs font-semibold uppercase tracking-widest text-indigo-400 mb-1">Test 1</p>
        <p class="text-gray-800"><?= esc_html($test1) ?></p>
    </div>
    <?php endif; ?>

    <?php $test2 = get_field('test_2'); if ($test2): ?>
    <div class="mt-4 p-6 bg-emerald-50 border border-emerald-200 rounded-xl">
        <p class="text-xs font-semibold uppercase tracking-widest text-emerald-400 mb-1">Test 2</p>
        <p class="text-gray-800"><?= esc_html($test2) ?></p>
    </div>
    <?php endif; ?>

    <?php $homeHero2 = get_theme_option('home-hero2'); if ($homeHero2): ?>
    <h2 class="text-2xl font-bold text-gray-900 mt-8"><?= esc_html($homeHero2) ?></h2>
    <?php endif; ?>

    <?php $heroDesc = get_theme_option('hero-description2'); if ($heroDesc): ?>
    <p class="text-gray-700 mt-4"><?= esc_html($heroDesc) ?></p>
    <?php endif; ?>

    <div class="text-gray-700 leading-relaxed space-y-4 mt-8">
        <?= get_the_content() ?>
    </div>

    <?php $heroButton2 = get_theme_option('hero-button2'); if ($heroButton2): ?>
    <a href="#" class="inline-block mt-6 px-6 py-3 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 transition">
        <?= esc_html($heroButton2) ?>
    </a>
    <?php endif; ?>

</div>

<?php include THEME_PATH . '/templates/footer.php'; ?>
