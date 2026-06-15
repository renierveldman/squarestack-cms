<?php
global $posts, $currentPage, $totalPages;

$blogSlug    = Settings::get('blog_slug', 'blog');
$perPage     = 9;
$currentPage = isset($currentPage) ? (int)$currentPage : 1;

if (empty($posts) || !is_array($posts)) {
    $result      = CMS::getPosts(['status' => 'published', 'limit' => $perPage, 'offset' => ($currentPage - 1) * $perPage]);
    $postItems   = $result['items'] ?? [];
    $totalPages  = $result['pages'] ?? 1;
} else {
    $postItems  = $posts['items'] ?? $posts;
    $totalPages = isset($totalPages) ? (int)$totalPages : 1;
}

$categoryMap = [];
foreach (CMS::getCategories() as $cat) {
    $categoryMap[(int)$cat['id']] = $cat;
}
?>
<?php include THEME_PATH . '/templates/header.php'; ?>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

    <h1 class="text-4xl font-bold text-gray-900 mb-10">Blog</h1>

    <?php if (empty($postItems)): ?>
    <p class="text-gray-500 text-lg">No posts found.</p>
    <?php else: ?>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <?php foreach ($postItems as $item):
            $permalink   = site_url('/' . $blogSlug . '/' . ($item['slug'] ?? ''));
            $excerpt     = strip_tags($item['excerpt'] ?? $item['content'] ?? '');
            if (mb_strlen($excerpt) > 120) $excerpt = mb_substr($excerpt, 0, 120) . '…';
            $catId       = isset($item['category_id']) ? (int)$item['category_id'] : 0;
            $cat         = $catId && isset($categoryMap[$catId]) ? $categoryMap[$catId] : null;
            $pubDate     = !empty($item['published_at']) ? $item['published_at'] : ($item['created_at'] ?? '');
        ?>
        <article class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden flex flex-col">

            <?php if (!empty($item['featured_image'])): ?>
            <a href="<?= esc_url($permalink) ?>" class="block overflow-hidden">
                <img src="<?= esc_url($item['featured_image']) ?>" alt="<?= esc_attr($item['title'] ?? '') ?>"
                    loading="lazy" class="w-full aspect-video object-cover">
            </a>
            <?php endif; ?>

            <div class="p-5 flex flex-col flex-grow">

                <?php if ($cat): ?>
                <span class="inline-block mb-3 px-2 py-1 text-xs font-semibold uppercase tracking-wide rounded bg-indigo-100 text-indigo-700 self-start">
                    <?= esc_html($cat['name'] ?? '') ?>
                </span>
                <?php endif; ?>

                <h2 class="text-xl font-semibold mb-2">
                    <a href="<?= esc_url($permalink) ?>" class="text-gray-900 hover:text-indigo-600 transition-colors">
                        <?= esc_html($item['title'] ?? '') ?>
                    </a>
                </h2>

                <?php if ($pubDate): ?>
                <p class="text-sm text-gray-500 mb-3"><?= date('M d, Y', strtotime($pubDate)) ?></p>
                <?php endif; ?>

                <?php if ($excerpt): ?>
                <p class="text-gray-700 mb-4 flex-grow"><?= esc_html($excerpt) ?></p>
                <?php endif; ?>

                <a href="<?= esc_url($permalink) ?>" class="mt-auto inline-block text-indigo-600 font-medium hover:underline">
                    Read more &rarr;
                </a>

            </div>
        </article>
        <?php endforeach; ?>
    </div>

    <?php pagination($currentPage, $totalPages, site_url('/' . $blogSlug)); ?>

    <?php endif; ?>

</main>

<?php include THEME_PATH . '/templates/footer.php'; ?>
