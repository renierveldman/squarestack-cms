<?php include THEME_PATH . '/templates/header.php'; ?>

<div class="max-w-4xl mx-auto px-4 py-12">

    <?php $headerImage = get_field('header_image'); if ($headerImage): ?>
    <img src="<?= esc_url($headerImage) ?>" alt="<?= esc_attr(get_the_title()) ?>" class="object-cover h-64 w-full rounded-xl mb-8" loading="lazy">
    <?php endif; ?>

    <h1 class="text-4xl font-bold text-gray-900"><?= esc_html(get_the_title()) ?></h1>

    <?php $subtitle = get_field('page_subtitle'); if ($subtitle): ?>
    <p class="text-xl text-gray-500 mt-2"><?= esc_html($subtitle) ?></p>
    <?php endif; ?>

    <div class="text-gray-700 leading-relaxed space-y-4 mt-8">
        <?= get_the_content() ?>
    </div>

</div>

<?php include THEME_PATH . '/templates/footer.php'; ?>
