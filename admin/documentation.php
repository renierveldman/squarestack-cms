<?php
require_once __DIR__ . '/../config.php';
require_once CORE_PATH . '/Database.php';
require_once CORE_PATH . '/Auth.php';
require_once CORE_PATH . '/Settings.php';

Auth::require();

$user        = Auth::currentUser();
$currentPage = 'documentation';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documentation &mdash; SquareStack CMS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Google+Sans:wght@400;500;700&family=Google+Sans+Display:wght@400;500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
    <style>
        body { font-family: 'Google Sans', 'Google Sans Display', system-ui, sans-serif; }
        .prose-doc h2 { font-size: 1.25rem; font-weight: 700; color: #111827; margin: 2rem 0 .75rem; padding-bottom: .5rem; border-bottom: 1px solid #e5e7eb; }
        .prose-doc h3 { font-size: 1rem; font-weight: 600; color: #374151; margin: 1.5rem 0 .5rem; }
        .prose-doc p  { font-size: .875rem; color: #4b5563; line-height: 1.75; margin: .5rem 0; }
        .prose-doc ul { list-style: disc; padding-left: 1.5rem; margin: .5rem 0; }
        .prose-doc li { font-size: .875rem; color: #4b5563; line-height: 1.75; }
        .prose-doc code { font-family: ui-monospace, monospace; font-size: .8rem; background: #f3f4f6; color: #1f2937; padding: .1rem .35rem; border-radius: .25rem; }
        .prose-doc pre  { background: #0f172a; color: #e2e8f0; font-size: .8rem; line-height: 1.7; padding: 1.25rem 1.5rem; border-radius: .5rem; overflow-x: auto; margin: .75rem 0 1rem; font-family: ui-monospace, monospace; }
        .prose-doc pre code { background: transparent; color: inherit; padding: 0; }
        .prose-doc .tag { display: inline-flex; align-items: center; gap: .3rem; font-size: .7rem; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; padding: .2rem .6rem; border-radius: 9999px; }
        .prose-doc table { width: 100%; border-collapse: collapse; font-size: .8rem; margin: .75rem 0 1rem; }
        .prose-doc th { background: #f9fafb; text-align: left; padding: .6rem .9rem; font-weight: 600; color: #374151; border: 1px solid #e5e7eb; }
        .prose-doc td { padding: .6rem .9rem; color: #4b5563; border: 1px solid #e5e7eb; vertical-align: top; }
        .prose-doc .note { background: #eff6ff; border-left: 3px solid #3b82f6; padding: .75rem 1rem; border-radius: 0 .375rem .375rem 0; margin: .75rem 0; }
        .prose-doc .warn { background: #fffbeb; border-left: 3px solid #f59e0b; padding: .75rem 1rem; border-radius: 0 .375rem .375rem 0; margin: .75rem 0; }
        /* Sidebar nav highlight */
        .doc-nav a.active { background: #eef2ff; color: #4f46e5; font-weight: 600; }
        .doc-nav a { display: block; padding: .4rem .75rem; border-radius: .375rem; font-size: .8rem; color: #6b7280; transition: background .15s; }
        .doc-nav a:hover { background: #f3f4f6; color: #111827; }
        .doc-nav .section-label { font-size: .65rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: #9ca3af; padding: 1rem .75rem .35rem; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex">

    <?php require __DIR__ . '/partials/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col min-h-screen">

        <!-- Top Bar -->
        <header class="bg-white shadow-sm border-b border-gray-200 px-8 py-4 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold text-gray-800">Documentation</h1>
                <p class="text-sm text-gray-500 mt-0.5">SquareStack CMS &mdash; Theme Development Guidelines</p>
            </div>
            <a href="<?= SITE_URL ?>/" target="_blank"
               class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors no-underline">
                <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
                View Site
            </a>
        </header>

        <!-- Body: two-column layout -->
        <div class="flex-1 flex">

            <!-- Doc Sidebar -->
            <nav class="w-56 flex-shrink-0 bg-white border-r border-gray-200 py-6 sticky top-0 h-screen overflow-y-auto doc-nav">
                <div class="section-label">Getting Started</div>
                <a href="#overview">Overview</a>
                <a href="#folder-structure">Folder Structure</a>
                <a href="#theme-config">Theme Config</a>

                <div class="section-label">Templates</div>
                <a href="#template-files">Template Files</a>
                <a href="#header-footer">Header &amp; Footer</a>
                <a href="#template-hierarchy">Template Hierarchy</a>

                <div class="section-label">Data & Fields</div>
                <a href="#custom-fields">Custom Fields</a>
                <a href="#field-types">Field Types</a>
                <a href="#theme-options">Theme Options</a>
                <a href="#helper-functions">Helper Functions</a>

                <div class="section-label">Features</div>
                <a href="#forms">Forms</a>
                <a href="#menus">Menus</a>
                <a href="#media">Media</a>
                <a href="#seo">SEO</a>
                <a href="#integrations">Integrations</a>

                <div class="section-label">Reference</div>
                <a href="#conventions">Conventions</a>
                <a href="#checklist">Launch Checklist</a>
            </nav>

            <!-- Doc Content -->
            <main class="flex-1 p-10 max-w-4xl prose-doc overflow-y-auto">

                <!-- ── OVERVIEW ─────────────────────────────────────────── -->
                <section id="overview">
                    <h2>Overview</h2>
                    <p>SquareStack CMS is a lightweight PHP 8+ content management system built for marketing agency clients. There are no Composer dependencies, no MVC framework, and no build step — just plain PHP, PDO, and Tailwind CDN. Themes live in <code>themes/{name}/</code> and are fully self-contained.</p>
                    <p>The active theme is set in <code>config.php</code>:</p>
                    <pre><code>define('THEME', 'starter');</code></pre>
                    <div class="note"><strong>Design principle:</strong> A theme only ever reads data from the CMS — it never writes. All writes go through admin pages or <code>form-handler.php</code>.</div>
                </section>

                <!-- ── FOLDER STRUCTURE ─────────────────────────────────── -->
                <section id="folder-structure">
                    <h2>Folder Structure</h2>
                    <pre><code>themes/
└── your-theme/
    ├── functions.php          # Required — field groups, hooks
    ├── templates/
    │   ├── header.php         # Shared head + nav
    │   ├── footer.php         # Shared footer + scripts
    │   ├── index.php          # Blog/post list fallback
    │   ├── single.php         # Single post
    │   ├── page.php           # Default page template
    │   ├── home.php           # Homepage (slug: home)
    │   ├── about.php          # About page
    │   ├── services.php       # Services page
    │   └── contact.php        # Contact page
    └── assets/                # Optional — CSS, JS, images</code></pre>
                    <p>Every template file maps to a page slug or post type. The router matches by slug first, then falls back to the template name set on the page record.</p>
                </section>

                <!-- ── THEME CONFIG ─────────────────────────────────────── -->
                <section id="theme-config">
                    <h2>Theme Config</h2>
                    <p><code>functions.php</code> is loaded automatically by the router before any template renders. Use it exclusively for:</p>
                    <ul>
                        <li>Registering custom field groups via <code>register_field_group()</code></li>
                        <li>Registering nav menu locations</li>
                        <li>Any theme-specific PHP helpers (keep them prefixed, e.g. <code>theme_render_card()</code>)</li>
                    </ul>
                    <div class="warn"><strong>Do not</strong> run database queries, output HTML, or start sessions inside <code>functions.php</code>. It runs on every page request.</div>
                </section>

                <!-- ── TEMPLATE FILES ───────────────────────────────────── -->
                <section id="template-files">
                    <h2>Template Files</h2>
                    <p>Every template receives two global variables injected by the router:</p>
                    <table>
                        <tr><th>Variable</th><th>Type</th><th>Description</th></tr>
                        <tr><td><code>$page</code></td><td>array|null</td><td>The current page row from the database (id, title, slug, content, template, meta_title, …)</td></tr>
                        <tr><td><code>$post</code></td><td>array|null</td><td>The current post row (on single post pages)</td></tr>
                    </table>
                    <p>Templates are plain PHP files. There is no templating language — use standard PHP with short echo tags.</p>
                    <pre><code>&lt;?php include THEME_PATH . '/templates/header.php'; ?&gt;

&lt;section&gt;
    &lt;h1&gt;&lt;?= esc_html($page['title']) ?&gt;&lt;/h1&gt;
    &lt;div&gt;&lt;?= $page['content'] ?&gt;&lt;/div&gt;
&lt;/section&gt;

&lt;?php include THEME_PATH . '/templates/footer.php'; ?&gt;</code></pre>
                </section>

                <!-- ── HEADER & FOOTER ──────────────────────────────────── -->
                <section id="header-footer">
                    <h2>Header &amp; Footer</h2>
                    <p>Include them at the top and bottom of every template. They are responsible for the full HTML document — <code>&lt;!DOCTYPE&gt;</code> through <code>&lt;/html&gt;</code>.</p>
                    <pre><code>&lt;?php include THEME_PATH . '/templates/header.php'; ?&gt;

&lt;!-- your page content --&gt;

&lt;?php include THEME_PATH . '/templates/footer.php'; ?&gt;</code></pre>

                    <h3>What header.php must contain</h3>
                    <ul>
                        <li><code>&lt;?php seo_head(); ?&gt;</code> — outputs title, meta description, OG tags</li>
                        <li><code>&lt;?php integrations_head(); ?&gt;</code> — outputs GTM/GA4/Meta Pixel scripts</li>
                        <li><code>&lt;?php google_fonts_head(); ?&gt;</code> — outputs Google Fonts link tags</li>
                        <li><code>&lt;?php integrations_body_open(); ?&gt;</code> — immediately after <code>&lt;body&gt;</code>, outputs GTM noscript iframe</li>
                    </ul>
                    <pre><code>&lt;head&gt;
    &lt;meta charset="UTF-8"&gt;
    &lt;meta name="viewport" content="width=device-width, initial-scale=1.0"&gt;
    &lt;?php seo_head(); ?&gt;
    &lt;?php integrations_head(); ?&gt;
    &lt;?php google_fonts_head(); ?&gt;
    &lt;!-- your CSS/scripts --&gt;
&lt;/head&gt;
&lt;body&gt;
&lt;?php integrations_body_open(); ?&gt;
&lt;!-- nav etc --&gt;</code></pre>
                </section>

                <!-- ── TEMPLATE HIERARCHY ───────────────────────────────── -->
                <section id="template-hierarchy">
                    <h2>Template Hierarchy</h2>
                    <p>The router resolves templates in this order:</p>
                    <table>
                        <tr><th>URL Pattern</th><th>Template Used</th></tr>
                        <tr><td><code>/</code> or <code>/home</code></td><td><code>templates/home.php</code></td></tr>
                        <tr><td><code>/{slug}</code> — page exists, template column set</td><td><code>templates/{template}.php</code></td></tr>
                        <tr><td><code>/{slug}</code> — page exists, no template set</td><td><code>templates/page.php</code></td></tr>
                        <tr><td><code>/blog</code> (or custom blog slug)</td><td><code>templates/index.php</code></td></tr>
                        <tr><td><code>/blog/{post-slug}</code></td><td><code>templates/single.php</code></td></tr>
                        <tr><td>No match</td><td><code>templates/404.php</code> (if exists) or a plain 404 string</td></tr>
                    </table>
                    <p>To create a new page template: add the PHP file in <code>templates/</code>, then select it in the admin when creating/editing a page.</p>
                </section>

                <!-- ── CUSTOM FIELDS ────────────────────────────────────── -->
                <section id="custom-fields">
                    <h2>Custom Fields</h2>
                    <p>Custom fields are registered in <code>functions.php</code> using <code>register_field_group()</code>. Fields are stored in the <code>meta</code> table and are scoped to a page template or to site-wide theme options.</p>

                    <h3>Page-scoped fields</h3>
                    <p>These appear in the admin when editing a page that uses the matching template slug.</p>
                    <pre><code>register_field_group([
    'title'    => 'About Page',
    'location' => ['type' => 'page', 'value' => 'about'],
    'template' => 'about',
    'fields'   => [
        ['name' => 'page_headline', 'label' => 'Page Headline', 'type' => 'text'],
        ['name' => 'page_intro',    'label' => 'Page Intro',    'type' => 'textarea'],
        ['name' => 'story_image',   'label' => 'Story Image',   'type' => 'image'],
    ],
]);</code></pre>
                    <p>Retrieve in your template with:</p>
                    <pre><code>&lt;?= esc_html(get_field('page_headline')) ?&gt;</code></pre>

                    <h3>Site-wide (theme option) fields</h3>
                    <p>These appear in the <strong>Custom Fields</strong> admin page and are available everywhere.</p>
                    <pre><code>register_field_group([
    'title'    => 'Contact Info',
    'location' => ['type' => 'option'],
    'fields'   => [
        ['name' => 'phone', 'label' => 'Phone Number', 'type' => 'tel'],
        ['name' => 'email', 'label' => 'Email Address', 'type' => 'email'],
    ],
]);</code></pre>
                    <p>Retrieve with:</p>
                    <pre><code>&lt;?= esc_html(get_theme_option('phone')) ?&gt;</code></pre>
                </section>

                <!-- ── FIELD TYPES ──────────────────────────────────────── -->
                <section id="field-types">
                    <h2>Field Types</h2>
                    <table>
                        <tr><th>Type</th><th>Admin UI</th><th>Returns</th></tr>
                        <tr><td><code>text</code></td><td>Single-line input</td><td>string</td></tr>
                        <tr><td><code>textarea</code></td><td>Multi-line textarea</td><td>string</td></tr>
                        <tr><td><code>email</code></td><td>Email input</td><td>string</td></tr>
                        <tr><td><code>tel</code></td><td>Phone input</td><td>string</td></tr>
                        <tr><td><code>url</code></td><td>URL input</td><td>string</td></tr>
                        <tr><td><code>image</code></td><td>Media library picker</td><td>URL string</td></tr>
                        <tr><td><code>toggle</code></td><td>On/Off switch</td><td><code>'1'</code> or <code>''</code></td></tr>
                        <tr><td><code>wysiwyg</code></td><td>Rich text editor</td><td>HTML string (do not escape)</td></tr>
                        <tr><td><code>select</code></td><td>Dropdown</td><td>string (selected value)</td></tr>
                        <tr><td><code>number</code></td><td>Number input</td><td>string</td></tr>
                        <tr><td><code>color</code></td><td>Colour picker</td><td>hex string</td></tr>
                    </table>
                    <div class="warn">Only <code>wysiwyg</code> fields return trusted HTML. All other field types should be output through <code>esc_html()</code> or <code>esc_attr()</code>.</div>
                </section>

                <!-- ── THEME OPTIONS ────────────────────────────────────── -->
                <section id="theme-options">
                    <h2>Theme Options</h2>
                    <p>Theme options are site-wide settings managed under <strong>Custom Fields</strong> in the admin. Use them for anything that belongs to the whole site: contact info, social links, branding, nav labels.</p>
                    <pre><code>// Retrieve a theme option (with optional default)
$phone = get_theme_option('phone', '');
$logo  = get_theme_option('logo');

// Retrieve the CMS site setting (site_name, site_tagline, etc.)
$siteName = get_site('site_name');</code></pre>
                    <p>The difference: <code>get_theme_option()</code> reads from <code>meta</code> (theme-registered fields). <code>get_site()</code> reads from <code>settings</code> (core CMS settings).</p>
                </section>

                <!-- ── HELPER FUNCTIONS ─────────────────────────────────── -->
                <section id="helper-functions">
                    <h2>Helper Functions</h2>
                    <p>All helpers are defined in <code>core/helpers.php</code> and available in every template automatically.</p>

                    <table>
                        <tr><th>Function</th><th>Description</th></tr>
                        <tr><td><code>get_field(string $key, mixed $default = '')</code></td><td>Returns a custom field value for the current page.</td></tr>
                        <tr><td><code>get_theme_option(string $key, mixed $default = '')</code></td><td>Returns a site-wide theme option value.</td></tr>
                        <tr><td><code>get_site(string $key)</code></td><td>Returns a core CMS setting (site_name, etc).</td></tr>
                        <tr><td><code>get_menu(string $location)</code></td><td>Returns the menu array for a registered location.</td></tr>
                        <tr><td><code>site_url(string $path = '')</code></td><td>Returns the full site URL with optional path.</td></tr>
                        <tr><td><code>esc_html(string $str)</code></td><td>Escapes a string for HTML output.</td></tr>
                        <tr><td><code>esc_attr(string $str)</code></td><td>Escapes a string for use in an HTML attribute.</td></tr>
                        <tr><td><code>esc_url(string $url)</code></td><td>Sanitises and escapes a URL.</td></tr>
                        <tr><td><code>seo_head()</code></td><td>Outputs meta title, description, OG tags. Call in <code>&lt;head&gt;</code>.</td></tr>
                        <tr><td><code>google_fonts_head()</code></td><td>Outputs Google Fonts link tags. Call in <code>&lt;head&gt;</code>.</td></tr>
                        <tr><td><code>integrations_head()</code></td><td>Outputs GTM/GA4/Meta Pixel scripts. Call in <code>&lt;head&gt;</code>.</td></tr>
                        <tr><td><code>integrations_body_open()</code></td><td>Outputs GTM noscript. Call immediately after <code>&lt;body&gt;</code>.</td></tr>
                        <tr><td><code>render_form(string $slug)</code></td><td>Renders a form by its slug. Includes inline AJAX submit logic.</td></tr>
                        <tr><td><code>get_posts(array $args)</code></td><td>Queries and returns an array of posts. Accepts status, limit, category_id, order.</td></tr>
                    </table>

                    <h3>get_posts() example</h3>
                    <pre><code>$posts = get_posts(['status' => 'published', 'limit' => 3]);
foreach ($posts as $post):
?&gt;
&lt;a href="&lt;?= site_url('/blog/' . $post['slug']) ?&gt;"&gt;
    &lt;?= esc_html($post['title']) ?&gt;
&lt;/a&gt;
&lt;?php endforeach; ?&gt;</code></pre>
                </section>

                <!-- ── FORMS ────────────────────────────────────────────── -->
                <section id="forms">
                    <h2>Forms</h2>
                    <p>Forms are built and managed in the <strong>Forms</strong> admin section. Each form has a slug. To embed a form in any template, call:</p>
                    <pre><code>&lt;?php render_form('contact'); ?&gt;</code></pre>
                    <p>The helper outputs the full form HTML and inline AJAX submit handling. Submissions are stored in the database and optionally forwarded to a notification email and/or a Mailchimp audience.</p>
                    <p>The form submits to <code>/form-handler.php</code> — a public (no-auth) endpoint. There is no need to handle submission logic in your template.</p>
                    <div class="note">Create the form first in the admin, note its slug, then drop <code>render_form('slug')</code> wherever you need it. A form can be reused across multiple pages and templates.</div>
                </section>

                <!-- ── MENUS ────────────────────────────────────────────── -->
                <section id="menus">
                    <h2>Menus</h2>
                    <p>Menu locations are created automatically when the CMS bootstraps (primary + footer). Retrieve and render a menu in your template:</p>
                    <pre><code>&lt;?php
$menu       = get_menu('primary');
$currentUri = strtok($_SERVER['REQUEST_URI'], '?');
foreach ($menu['items'] as $item):
    $isActive = rtrim($currentUri, '/') === rtrim(parse_url($item['url'], PHP_URL_PATH), '/');
?&gt;
&lt;a href="&lt;?= esc_url($item['url']) ?&gt;"
   class="&lt;?= $isActive ? 'active' : '' ?&gt;"&gt;
    &lt;?= esc_html($item['label']) ?&gt;
&lt;/a&gt;
&lt;?php endforeach; ?&gt;</code></pre>
                    <p>Each <code>$item</code> has: <code>id</code>, <code>label</code>, <code>url</code>, <code>target</code> (<code>_self</code>|<code>_blank</code>), <code>sort_order</code>.</p>
                </section>

                <!-- ── MEDIA ────────────────────────────────────────────── -->
                <section id="media">
                    <h2>Media</h2>
                    <p>Images uploaded through the media library are stored in <code>uploads/{year}/{month}/</code> and automatically converted to WebP (85% quality). Image fields return the full URL of the WebP version.</p>
                    <pre><code>&lt;?php $heroImage = get_field('hero_image'); ?&gt;
&lt;?php if ($heroImage): ?&gt;
    &lt;img src="&lt;?= esc_url($heroImage) ?&gt;" alt="" loading="lazy"&gt;
&lt;?php endif; ?&gt;</code></pre>
                    <p>Always guard image fields with an <code>if</code> check — they may be empty before the client adds their content.</p>
                </section>

                <!-- ── SEO ──────────────────────────────────────────────── -->
                <section id="seo">
                    <h2>SEO</h2>
                    <p><code>seo_head()</code> generates all SEO tags automatically. It reads from the page record first, then falls back to the site-wide defaults in <strong>Settings → SEO</strong>.</p>
                    <table>
                        <tr><th>Tag</th><th>Source (page-level)</th><th>Fallback (site-wide)</th></tr>
                        <tr><td><code>&lt;title&gt;</code></td><td><code>pages.meta_title</code></td><td><code>pages.title + seo_title_suffix</code></td></tr>
                        <tr><td><code>meta description</code></td><td><code>pages.meta_description</code></td><td><code>seo_meta_desc</code> setting</td></tr>
                        <tr><td><code>og:image</code></td><td><code>pages.og_image</code></td><td><code>seo_og_image</code> setting</td></tr>
                    </table>
                    <div class="note">You do not need to write any <code>&lt;title&gt;</code> or <code>&lt;meta&gt;</code> tags yourself — <code>seo_head()</code> handles everything as long as it is called in <code>&lt;head&gt;</code>.</div>
                </section>

                <!-- ── INTEGRATIONS ─────────────────────────────────────── -->
                <section id="integrations">
                    <h2>Integrations</h2>
                    <p>Marketing integrations (GA4, GTM, Meta Pixel, Google Search Console) are configured in <strong>Integrations</strong> in the admin. Your theme does not need to hardcode any tracking IDs.</p>
                    <p>The two required hook calls do all the work:</p>
                    <pre><code>&lt;!-- In &lt;head&gt; --&gt;
&lt;?php integrations_head(); ?&gt;

&lt;!-- Immediately after &lt;body&gt; --&gt;
&lt;?php integrations_body_open(); ?&gt;</code></pre>
                    <p><strong>Mailchimp:</strong> connected via <strong>Integrations</strong>. When a form has an audience selected, the CMS subscribes the submitter automatically — no extra template code required.</p>
                </section>

                <!-- ── CONVENTIONS ──────────────────────────────────────── -->
                <section id="conventions">
                    <h2>Conventions</h2>

                    <h3>Always escape output</h3>
                    <pre><code>&lt;!-- Correct --&gt;
&lt;?= esc_html(get_field('title')) ?&gt;
&lt;a href="&lt;?= esc_url(get_field('cta_url')) ?&gt;"&gt;...&lt;/a&gt;

&lt;!-- Only skip escaping for wysiwyg fields --&gt;
&lt;?= get_field('page_content') /* trusted HTML */ ?&gt;</code></pre>

                    <h3>Use hardcoded fallback content</h3>
                    <p>Every <code>get_field()</code> call should have a sensible fallback so the page renders gracefully before the client has filled in their content:</p>
                    <pre><code>$headline = get_field('hero_title') ?: 'Your Compelling Headline Here';</code></pre>

                    <h3>Field name conventions</h3>
                    <ul>
                        <li>Use <code>snake_case</code> for all field names: <code>hero_title</code>, <code>story_image</code></li>
                        <li>Prefix page-specific fields with context when names could clash: <code>about_story_image</code> vs <code>services_hero_image</code></li>
                        <li>Boolean/toggle fields: prefix with <code>show_</code> or <code>enable_</code>: <code>show_latest_posts</code></li>
                    </ul>

                    <h3>Styling</h3>
                    <ul>
                        <li>Use Tailwind CDN with a config block in <code>header.php</code> for custom brand colours</li>
                        <li>Define brand colours in the Tailwind config — do not use inline <code>style</code> attributes for colours</li>
                        <li>The starter theme uses <code>brand-black: #0a0a0a</code> and Inter via Google Fonts as defaults</li>
                        <li>Keep section alternation consistent: dark hero → white content → dark section → white CTA</li>
                    </ul>

                    <h3>No business logic in templates</h3>
                    <p>Templates retrieve data and render HTML. If you need complex data manipulation, add a helper function in <code>functions.php</code> or <code>core/helpers.php</code>.</p>
                </section>

                <!-- ── LAUNCH CHECKLIST ─────────────────────────────────── -->
                <section id="checklist">
                    <h2>Launch Checklist</h2>
                    <table>
                        <tr><th>Area</th><th>Task</th></tr>
                        <tr><td>Settings</td><td>Set site name, tagline, SEO title suffix, default meta description, and OG image</td></tr>
                        <tr><td>Settings</td><td>Set blog slug if using the blog feature</td></tr>
                        <tr><td>Pages</td><td>Create all required pages with the correct template assigned</td></tr>
                        <tr><td>Menus</td><td>Build the Primary Navigation menu with all page links</td></tr>
                        <tr><td>Custom Fields</td><td>Fill in Contact Info, Social Media, Branding, and Nav Options</td></tr>
                        <tr><td>Media</td><td>Upload logo (SVG or WebP recommended)</td></tr>
                        <tr><td>Forms</td><td>Create a Contact form and add a notification email</td></tr>
                        <tr><td>Integrations</td><td>Connect GA4 or GTM, Meta Pixel, Google Search Console</td></tr>
                        <tr><td>SEO</td><td>Verify each page has a meta title and description set</td></tr>
                        <tr><td>Theme</td><td>Replace all demo placeholder images with real client images</td></tr>
                        <tr><td>Theme</td><td>Replace all hardcoded fallback copy with real client content via Custom Fields</td></tr>
                        <tr><td>Security</td><td>Change default admin password before handing over to client</td></tr>
                        <tr><td>Hosting</td><td>Update <code>config.php</code> with production DB credentials</td></tr>
                        <tr><td>Hosting</td><td>Ensure <code>uploads/</code> and <code>cache/</code> directories are writable</td></tr>
                    </table>
                </section>

                <div class="h-16"></div>

            </main>
        </div><!-- /two-col -->
    </div><!-- /main content -->

</body>
<script>
// Highlight the active doc nav item based on scroll position
(function () {
    var links   = document.querySelectorAll('.doc-nav a[href^="#"]');
    var sections = Array.from(links).map(function (l) {
        return document.querySelector(l.getAttribute('href'));
    });

    function onScroll() {
        var scrollY = window.scrollY || document.documentElement.scrollTop;
        var active  = 0;
        sections.forEach(function (sec, i) {
            if (sec && sec.offsetTop - 120 <= scrollY) {
                active = i;
            }
        });
        links.forEach(function (l) { l.classList.remove('active'); });
        if (links[active]) { links[active].classList.add('active'); }
    }

    // Scroll happens inside <main>, not window — attach to it
    var mainEl = document.querySelector('main');
    if (mainEl) {
        mainEl.addEventListener('scroll', onScroll, { passive: true });
    }

    links.forEach(function (link) {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            var target = document.querySelector(link.getAttribute('href'));
            if (target && mainEl) {
                mainEl.scrollTo({ top: target.offsetTop - 32, behavior: 'smooth' });
            }
        });
    });
}());
</script>
</html>
