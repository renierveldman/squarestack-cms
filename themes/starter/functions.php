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
        [
            'name'    => 'show_latest_posts',
            'label'   => 'Show Latest Posts',
            'type'    => 'toggle',
            'default' => false,
        ],
        [
            'name'  => 'latest_posts_heading',
            'label' => 'Latest Posts Heading',
            'type'  => 'text',
        ],
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
