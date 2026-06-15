<?php

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
