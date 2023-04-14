<?php

return [
    'default_social_image' => null,

    'humans_txt' => [
        'path' => base_path('humans.txt'),
        'enabled' => true,
    ],

    'robots_txt' => [
        'path' => base_path('robots.txt'),
        'enabled' => true,
    ],

    'security_txt' => [
        'path' => base_path('security.txt'),
        'enabled' => true,
    ],

    'favicon' => [
        'enabled' => false,
    ],

    'global' => [
    
        'enable_tags' => false,
        
        'minify_html' => false,
        
        'app_name' => null,
        
        'app_name_pos' => null,
        
        'separator' => null,
        
        'app_title' => null,
        
        'app_description' => null,

    ],

    'models_to_attach' => [
        \Winter\Blog\Models\Post::class,
        \Winter\Blog\Models\Category::class,
    ],
];
