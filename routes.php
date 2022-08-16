<?php

use Route;
use Response;
use Winter\SEO\Models\Settings;

// @TODO: Test
$txtResponse = function ($key) {
    $contents = Settings::get($key);

    // Fall back to loading from config
    // @TODO: Move into settings model perhaps?
    if (empty($contents)) {
        $path = Config::get("winter.seo::config.{$key}.path");
        if (file_exists($path)) {
            $contents = file_get_contents($path);
        }
    }

    return Response::make($contents, 200, ['Content-Type' => 'text/plain']);
};

Route::get('/humans.txt', $txtResponse('humans_txt'));
Route::get('/robots.txt', $txtResponse('robots_txt'));
Route::get('/security.txt', $txtResponse('security_txt'));
Route::get('/.well-known/security.txt', $txtResponse('security_txt'));
