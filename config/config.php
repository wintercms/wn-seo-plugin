<?php

$resolvePath = function ($file) {
    $env = env('APP_ENV', 'production');
    $path = base_path($env . '.' . $file);
    if (!file_exists($path)) {
        $path = base_path($file);
    }

    return $path;
};

return [
    /*
    |--------------------------------------------------------------------------
    | Default Social Image
    |--------------------------------------------------------------------------
    |
    | The default image that should be used as the og:image preview across
    | the site. Any value that the ImageResizer::filterGetUrl()
    | static method accepts is valid here.
    |
    */

    'default_social_image' => null,

    /*
    |--------------------------------------------------------------------------
    | Well-Known TXT Files
    |--------------------------------------------------------------------------
    |
    | List of well-known TXT files that should served from the root of the
    | site. These files are used to provide information to other services
    | such as search engines and security researchers.
    |
    | Each file can be configured with a 'path' to a custom file, or with
    | the 'content' value containing the raw text content to deliver.
    | The content provided in the backend settings takes priority.
    |
    */

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

    /*
    |--------------------------------------------------------------------------
    | Automatically add the SEO fields to SeoableModel forms
    |--------------------------------------------------------------------------
    |
    | By default, the SEO fields will be automatically added to any
    | forms whose model's implement the SeoableModel behaviour.
    | Set this to false in order to disable that behaviour.
    |
    */

    'autoInjectSeoFields' => true,

    /*
    |--------------------------------------------------------------------------
    | Seoable Models
    |--------------------------------------------------------------------------
    |
    | Models defined here will be automatically extended by the Winter.SEO
    | plugin to include the SEO fields on the model's forms and the
    | SeoableModel behaviour will be attached to the model.
    |
    */

    'seoableModels' => [
        // Example of single model having default settings applied
        // \Cms\Models\ThemeData::class,

        // Example of per model configuration options
        // \Winter\Blog\Models\Post::class => [
        //     'data_column' => 'metadata',
        //     'meta_from' => [
        //         'og:title' => 'title',
        //         'og:description' => 'summary',
        //         'og:image' => 'featured_images',
        //         'og:type' => 'article',
        //     ],
        //     'link_from' => [
        //         'robots' => 'nofollow',
        //     ],
        // ],
    ],

];
