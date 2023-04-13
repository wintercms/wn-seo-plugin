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
        'path' => base_path('favicon.ico'),
        'enabled' => false,
    ],

    'blobal' => [
    
        'enable_tags' => false,
        
        'minify_html' => false,
        
        'app_name' => null,
        
        'app_name_pos' => null,
        
        'separator' => null,
        
        'app_title' => null,
        
        'app_description' => null,

    ],
];
