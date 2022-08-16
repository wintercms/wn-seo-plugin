<?php

return [
    'plugin' => [
        'name' => 'SEO',
        'description' => 'Manage SEO tags for your website.',
    ],
    'meta' => [
        'og:image:alt' => 'Social image for :title on :app_name',
    ],
    'models' => [
        'link' => [
            'label' => 'Link',
            'label_plural' => 'Link Tags',
            'comment' => 'Manage global link tags that will appear on the entire site',
            'prompt' => 'Add new link tag',
            'rel' => 'Rel',
            'href' => 'Href',
            'description' => 'Optional description of this link tag',
        ],
        'meta' => [
            'label' => 'Meta',
            'label_plural' => 'Meta Tags',
            'instructions' => 'Use these fields to set custom values that will be used in search engines and on social media platforms',
            'comment' => 'Manage global meta tags that will appear on the entire site',
            'prompt' => 'Add new meta tag',
            'name' => 'Name',
            'value' => 'Value',
            'description' => 'Optional description of this meta tag',
            'fields' => [
                'title' => 'Title',
                'description' => 'Description',
                'image' => 'Image',
                'nofollow' => 'Tell search engines to ignore links in content',
            ],
        ],
    ],
    'permissions' => [
        'manage_meta' => 'Manage SEO meta tags',
    ],
];
