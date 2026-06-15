<?php include THEME_PATH . '/templates/header.php'; ?>

<?php
$hero_title          = get_field('hero_title');
$hero_subtitle       = get_field('hero_subtitle');
$hero_image          = get_field('hero_image');
$cta_text            = get_field('cta_text');
$cta_url             = get_field('cta_url');
$show_latest_posts   = get_field('show_latest_posts');
$latest_posts_heading = get_field('latest_posts_heading');
$blogSlug            = get_site('blog_slug') ?: 'blog';
?>

<!-- Hero -->
<section class="relative w-full bg-indigo-600 text-white"<?php if ($hero_image): ?> style="background-image:url('<?= esc_url($hero_image) ?>');background-size:cover;background-position:center"<?php endif; ?>>
    <?php if ($hero_image): ?>
    <div class="absolute inset-0 bg-indigo-900 opacity-60"></div>
    <?php endif; ?>
    <div class="relative z-10 max-w-5xl mx-auto px-6 py-28 text-center">
        <?php if ($hero_title): ?>
            <h1 class="text-5xl font-bold mb-4"><?= esc_html($hero_title) ?></h1>
            <?php if ($hero_subtitle): ?>
            <p class="text-xl mb-8 text-indigo-100"><?= esc_html($hero_subtitle) ?></p>
            <?php endif; ?>
            <?php if ($cta_text && $cta_url): ?>
            <a href="<?= esc_url($cta_url) ?>" class="inline-block px-8 py-3 bg-white text-indigo-600 font-semibold rounded-lg hover:bg-indigo-50 transition">
                <?= esc_html($cta_text) ?>
            </a>
            <?php endif; ?>
        <?php else: ?>
            <h1 class="text-5xl font-bold mb-4"><?= esc_html(get_the_title()) ?></h1>
        <?php endif; ?>
    </div>
</section>

<!-- Latest Posts -->
<?php if ($show_latest_posts):
    $latest_posts = get_posts(['limit' => 3, 'status' => 'published']);
?>
<section class="py-16 bg-white">
    <div class="max-w-5xl mx-auto px-6">
        <?php if ($latest_posts_heading): ?>
        <h2 class="text-3xl font-bold mb-10 text-gray-800"><?= esc_html($latest_posts_heading) ?></h2>
        <?php endif; ?>
        <?php if ($latest_posts): ?>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <?php foreach ($latest_posts as $lp):
                $permalink = site_url('/' . $blogSlug . '/' . ($lp['slug'] ?? ''));
                $excerpt   = strip_tags($lp['excerpt'] ?? $lp['content'] ?? '');
                $excerpt   = mb_strimwidth($excerpt, 0, 100, '…');
            ?>
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden flex flex-col">
                <?php if (!empty($lp['featured_image'])): ?>
                <a href="<?= esc_url($permalink) ?>" class="block overflow-hidden">
                    <img src="<?= esc_url($lp['featured_image']) ?>" alt="<?= esc_attr($lp['title'] ?? '') ?>" class="w-full h-48 object-cover" loading="lazy">
                </a>
                <?php endif; ?>
                <div class="p-5 flex flex-col flex-grow">
                    <h3 class="text-xl font-semibold mb-2">
                        <a href="<?= esc_url($permalink) ?>" class="text-gray-900 hover:text-indigo-600 transition-colors">
                            <?= esc_html($lp['title'] ?? '') ?>
                        </a>
                    </h3>
                    <?php if (!empty($lp['published_at'] ?? $lp['created_at'])): ?>
                    <p class="text-sm text-gray-500 mb-3"><?= date('F j, Y', strtotime($lp['published_at'] ?? $lp['created_at'])) ?></p>
                    <?php endif; ?>
                    <p class="text-gray-700 mb-4 flex-grow"><?= esc_html($excerpt) ?></p>
                    <a href="<?= esc_url($permalink) ?>" class="mt-auto text-indigo-600 font-medium hover:underline">Read more &rarr;</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="mt-10 text-center">
            <a href="<?= esc_url(site_url('/' . $blogSlug)) ?>" class="inline-block px-6 py-3 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 transition">
                View all posts
            </a>
        </div>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>

<!-- Page content (if any) -->
<?php $content = get_the_content(); if ($content): ?>
<section class="py-16 bg-gray-50">
    <div class="max-w-5xl mx-auto px-6 leading-relaxed space-y-4 text-gray-700">
        <?= $content ?>
    </div>
</section>
<?php endif; ?>

<?php include THEME_PATH . '/templates/footer.php'; ?>
