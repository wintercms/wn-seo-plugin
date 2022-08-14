<?php

return [
    'plugin' => [
        'name' => 'SEO',
        'description' => 'Easily manage SEO tags for your pages.',
    ],
    'meta' => [
        'og:image:alt' => 'Social image for :title on :app_name',
    ],
    'models' => [
        'meta' => [
            'label' => 'Meta',
            'label_plural' => 'Meta Tags',
            'instructions' => 'Use these fields to set custom values that will be used in search engines and on social media platforms',
            'title' => 'Title',
            'description' => 'Description',
            'image' => 'Image',
            'nofollow' => 'Tell search engines to ignore links in content',
        ],
    ],
    'permissions' => [
        'some_permission' => 'Some permission',
    ],
];
