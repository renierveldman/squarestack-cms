<?php

// ─── Theme Options ────────────────────────────────────────────────────────────

register_field_group([
    'title'    => 'Contact Info',
    'location' => ['type' => 'option'],
    'fields'   => [
        [
            'name'        => 'phone',
            'label'       => 'Phone Number',
            'type'        => 'tel',
            'placeholder' => '+1 (555) 000-0000',
        ],
        [
            'name'        => 'email',
            'label'       => 'Email Address',
            'type'        => 'email',
            'placeholder' => 'hello@example.com',
        ],
        [
            'name'        => 'address',
            'label'       => 'Address',
            'type'        => 'textarea',
            'rows'        => 2,
            'placeholder' => '123 Main St, City, Country',
        ],
    ],
]);

register_field_group([
    'title'    => 'Social Media',
    'location' => ['type' => 'option'],
    'fields'   => [
        [
            'name'        => 'social_facebook',
            'label'       => 'Facebook URL',
            'type'        => 'url',
            'placeholder' => 'https://facebook.com/yourpage',
        ],
        [
            'name'        => 'social_instagram',
            'label'       => 'Instagram URL',
            'type'        => 'url',
            'placeholder' => 'https://instagram.com/yourhandle',
        ],
        [
            'name'        => 'social_linkedin',
            'label'       => 'LinkedIn URL',
            'type'        => 'url',
            'placeholder' => 'https://linkedin.com/company/yourpage',
        ],
        [
            'name'        => 'social_twitter',
            'label'       => 'X / Twitter URL',
            'type'        => 'url',
            'placeholder' => 'https://x.com/yourhandle',
        ],
    ],
]);

register_field_group([
    'title'    => 'Homepage Content',
    'location' => ['type' => 'option'],
    'fields'   => [
        [
            'name'        => 'home-hero2',
            'label'       => 'Home Title',
            'type'        => 'text',
            'placeholder' => '',
        ],
        [
            'name'        => 'hero-description2',
            'label'       => 'Hero Description',
            'type'        => 'textarea',
            'placeholder' => 'Please add text',
        ],
        [
            'name'        => 'hero-button2',
            'label'       => 'Hero Button 2',
            'type'        => 'text',
            'placeholder' => 'Please add text',
        ],
    ],
]);

register_field_group([
    'title'    => 'Branding',
    'location' => ['type' => 'option'],
    'fields'   => [
        [
            'name'  => 'logo',
            'label' => 'Logo Image',
            'type'  => 'image',
        ],
        [
            'name'        => 'tagline',
            'label'       => 'Site Tagline',
            'type'        => 'text',
            'placeholder' => 'Your short site tagline',
        ],
    ],
]);

// ─── Page Field Groups ─────────────────────────────────────────────────────────

register_field_group([
    'title'    => 'Demo Fields',
    'location' => ['type' => 'page', 'value' => 'demo'],
    'template' => 'demo',
    'fields'   => [
        [
            'name'        => 'test_1',
            'label'       => 'Test 1',
            'type'        => 'text',
            'placeholder' => 'Enter test value 1',
        ],
        [
            'name'        => 'test_2',
            'label'       => 'Test 2',
            'type'        => 'text',
            'placeholder' => 'Enter test value 2',
        ],
    ],
]);

register_field_group([
    'title'    => 'Home Page',
    'location' => [
        'type'  => 'page',
        'value' => 'home',
    ],
    'template' => 'home',
    'fields'   => [
        [
            'name'  => 'hero_title',
            'label' => 'Hero Title',
            'type'  => 'text',
        ],
        [
            'name'  => 'hero_subtitle',
            'label' => 'Hero Subtitle',
            'type'  => 'textarea',
        ],
        [
            'name'  => 'hero_image',
            'label' => 'Hero Image',
            'type'  => 'image',
        ],
        [
            'name'  => 'cta_text',
            'label' => 'CTA Text',
            'type'  => 'text',
        ],
        [
            'name'  => 'cta_url',
            'label' => 'CTA URL',
            'type'  => 'url',
        ],
        ['name' => 'show_latest_posts',   'label' => 'Show Latest Posts',   'type' => 'toggle', 'default' => false],
        ['name' => 'latest_posts_heading', 'label' => 'Latest Posts Heading', 'type' => 'text'],
        ['name' => 'stat_number',  'label' => 'Hero Stat Number', 'type' => 'text', 'placeholder' => '100%'],
        ['name' => 'stat_label',   'label' => 'Hero Stat Label',  'type' => 'text', 'placeholder' => 'Client satisfaction'],
        ['name' => 'div_1_title',  'label' => 'Division 1 Title', 'type' => 'text'],
        ['name' => 'div_1_desc',   'label' => 'Division 1 Desc',  'type' => 'textarea'],
        ['name' => 'div_2_title',  'label' => 'Division 2 Title', 'type' => 'text'],
        ['name' => 'div_2_desc',   'label' => 'Division 2 Desc',  'type' => 'textarea'],
        ['name' => 'div_3_title',  'label' => 'Division 3 Title', 'type' => 'text'],
        ['name' => 'div_3_desc',   'label' => 'Division 3 Desc',  'type' => 'textarea'],
        ['name' => 'why_image',    'label' => 'Why Us Image',     'type' => 'image'],
        ['name' => 'why_headline', 'label' => 'Why Us Headline',  'type' => 'text'],
        ['name' => 'why_body_1',   'label' => 'Why Us Para 1',    'type' => 'textarea'],
        ['name' => 'why_body_2',   'label' => 'Why Us Para 2',    'type' => 'textarea'],
        ['name' => 'why_point',    'label' => 'Why Us Point',     'type' => 'text'],
        ['name' => 'proof_number', 'label' => 'Proof Number',     'type' => 'text'],
        ['name' => 'proof_label',  'label' => 'Proof Label',      'type' => 'text'],
        ['name' => 'stat_1_num',   'label' => 'Stat 1 Number',    'type' => 'text'],
        ['name' => 'stat_1_label', 'label' => 'Stat 1 Label',     'type' => 'text'],
        ['name' => 'stat_2_num',   'label' => 'Stat 2 Number',    'type' => 'text'],
        ['name' => 'stat_2_label', 'label' => 'Stat 2 Label',     'type' => 'text'],
        ['name' => 'stat_3_num',   'label' => 'Stat 3 Number',    'type' => 'text'],
        ['name' => 'stat_3_label', 'label' => 'Stat 3 Label',     'type' => 'text'],
        ['name' => 'stat_4_num',   'label' => 'Stat 4 Number',    'type' => 'text'],
        ['name' => 'stat_4_label', 'label' => 'Stat 4 Label',     'type' => 'text'],
        ['name' => 'cta_headline', 'label' => 'Bottom CTA Headline', 'type' => 'text'],
        ['name' => 'cta_subtext',  'label' => 'Bottom CTA Subtext',  'type' => 'textarea'],
    ],
]);

register_field_group([
    'title'    => 'Default Page',
    'location' => [
        'type'  => 'page',
        'value' => 'page',
    ],
    'template' => 'page',
    'fields'   => [
        [
            'name'  => 'header_image',
            'label' => 'Header Image',
            'type'  => 'image',
        ],
        [
            'name'  => 'page_subtitle',
            'label' => 'Page Subtitle',
            'type'  => 'text',
        ],
    ],
]);

register_field_group([
    'title'    => 'Nav Options',
    'location' => ['type' => 'option'],
    'fields'   => [
        ['name' => 'nav_cta_text', 'label' => 'Nav CTA Text', 'type' => 'text',  'placeholder' => 'Get in Touch'],
        ['name' => 'nav_cta_url',  'label' => 'Nav CTA URL',  'type' => 'url',   'placeholder' => '/contact'],
    ],
]);

register_field_group([
    'title'    => 'About Page',
    'location' => ['type' => 'page', 'value' => 'about'],
    'template' => 'about',
    'fields'   => [
        ['name' => 'page_headline', 'label' => 'Page Headline',  'type' => 'text'],
        ['name' => 'page_intro',    'label' => 'Page Intro',     'type' => 'textarea'],
        ['name' => 'story_image',   'label' => 'Story Image',    'type' => 'image'],
        ['name' => 'story_headline','label' => 'Story Headline', 'type' => 'text'],
        ['name' => 'story_body_1',  'label' => 'Story Para 1',   'type' => 'textarea'],
        ['name' => 'story_body_2',  'label' => 'Story Para 2',   'type' => 'textarea'],
    ],
]);

register_field_group([
    'title'    => 'Services Page',
    'location' => ['type' => 'page', 'value' => 'services'],
    'template' => 'services',
    'fields'   => [
        ['name' => 'page_headline', 'label' => 'Page Headline', 'type' => 'text'],
        ['name' => 'page_intro',    'label' => 'Page Intro',    'type' => 'textarea'],
    ],
]);

register_field_group([
    'title'    => 'Contact Page',
    'location' => [
        'type'  => 'page',
        'value' => 'contact',
    ],
    'template' => 'contact',
    'fields'   => [
        [
            'name'  => 'contact_intro',
            'label' => 'Contact Intro',
            'type'  => 'textarea',
        ],
        [
            'name'  => 'contact_email',
            'label' => 'Contact Email',
            'type'  => 'text',
        ],
        [
            'name'  => 'contact_phone',
            'label' => 'Contact Phone',
            'type'  => 'text',
        ],
        [
            'name'  => 'map_embed',
            'label' => 'Map Embed',
            'type'  => 'textarea',
        ],
    ],
]);
