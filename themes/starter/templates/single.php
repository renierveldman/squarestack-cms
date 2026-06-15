<?php
global $post;

$blogSlug  = Settings::get('blog_slug', 'blog');
$pubDate   = !empty($post['published_at']) ? date('F j, Y', strtotime($post['published_at'])) : date('F j, Y', strtotime($post['created_at'] ?? 'now'));
$content   = $post['content'] ?? '';
$wordCount = str_word_count(strip_tags($content));
$readTime  = max(1, (int)round($wordCount / 200));

$cat = null;
if (!empty($post['category_id'])) {
    $cat = CMS::getCategory((int)$post['category_id']);
}

$pageUrl   = urlencode(site_url('/' . $blogSlug . '/' . ($post['slug'] ?? '')));
$pageTitle = urlencode($post['title'] ?? '');

$shareTwitter  = 'https://twitter.com/intent/tweet?url=' . $pageUrl . '&text=' . $pageTitle;
$shareFacebook = 'https://www.facebook.com/sharer/sharer.php?u=' . $pageUrl;
$shareLinkedIn = 'https://www.linkedin.com/shareArticle?mini=true&url=' . $pageUrl . '&title=' . $pageTitle;
?>
<?php include THEME_PATH . '/templates/header.php'; ?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

    <?php if (!empty($post['featured_image'])): ?>
    <img src="<?= esc_url($post['featured_image']) ?>" alt="<?= esc_attr($post['title'] ?? '') ?>"
        class="w-full h-80 object-cover rounded-xl mb-8" loading="lazy" width="800" height="320">
    <?php endif; ?>

    <?php if ($cat): ?>
    <span class="inline-block bg-indigo-100 text-indigo-700 text-xs font-semibold uppercase tracking-wide px-3 py-1 rounded-full mb-4">
        <?= esc_html($cat['name'] ?? '') ?>
    </span>
    <?php endif; ?>

    <h1 class="text-4xl font-bold leading-tight text-gray-900"><?= esc_html($post['title'] ?? '') ?></h1>

    <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500 mt-4">
        <span><?= date('F j, Y', strtotime($post['published_at'] ?? $post['created_at'] ?? 'now')) ?></span>
        <span>&middot;</span>
        <span><?= $readTime ?> min read</span>
    </div>

    <div class="mt-8 leading-relaxed space-y-4 text-gray-700">
        <?= $content ?>
    </div>

    <!-- Share -->
    <div class="mt-12 pt-8 border-t border-gray-200">
        <p class="text-sm font-semibold text-gray-600 mb-3">Share this post</p>
        <div class="flex gap-4">
            <a href="<?= esc_url($shareTwitter) ?>" target="_blank" rel="noopener" class="text-sm text-sky-500 hover:underline">Twitter / X</a>
            <a href="<?= esc_url($shareFacebook) ?>" target="_blank" rel="noopener" class="text-sm text-blue-600 hover:underline">Facebook</a>
            <a href="<?= esc_url($shareLinkedIn) ?>" target="_blank" rel="noopener" class="text-sm text-blue-700 hover:underline">LinkedIn</a>
        </div>
    </div>

    <div class="mt-8">
        <a href="<?= esc_url(site_url('/' . $blogSlug)) ?>" class="text-sm text-gray-600 hover:text-gray-900">&larr; Back to Blog</a>
    </div>

</div>

<?php include THEME_PATH . '/templates/footer.php'; ?>
