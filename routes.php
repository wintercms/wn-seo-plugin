<?php

use Winter\SEO\Models\Settings;

Event::listen('system.beforeRoute', function () {
    $txtResponse = function ($key) {
        $cacheKey = 'winter.seo::txt_response.' . $key;
        $contents = '';

        if (Config::get('app.debug')) {
            Cache::forget($cacheKey);
        }

        $contents = Cache::rememberForever($cacheKey, function () use ($key) {
            return Twig::parse(Settings::get($key), [
                'app_url' => Config::get('app.url'),
                'app_name' => Config::get('app.name'),
                'app_debug' => Config::get('app.debug'),
            ]);
        });

        if (empty($contents)) {
            return Response::make((new \Cms\Classes\Controller())->run('404'), 404);
        }

        return Response::make($contents, 200, ['Content-Type' => 'text/plain']);
    };

    Route::get('/humans.txt', fn() => $txtResponse('humans_txt'));
    Route::get('/robots.txt', fn() => $txtResponse('robots_txt'));
    Route::get('/security.txt', fn() => $txtResponse('security_txt'));
    Route::get('/.well-known/security.txt', fn() => $txtResponse('security_txt'));
});
