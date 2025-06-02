<?php

use Winter\SEO\Models\Settings;
use System\Classes\ImageResizer;
use Cms\Classes\CmsController;
use File as FileManager;

CmsController::extend(function($controller) {
  if(Settings::getOrDefault('global.minify_html')) {
    $controller->middleware('Winter\SEO\Middleware\CompressHTML');
  }
});

Event::listen('backend.page.beforeDisplay', function($controller, $action, $params) {
    $controller->addJs('/plugins/winter/seo/assets/counter.js');
});

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
    if(Settings::getOrDefault('humans_txt.enabled')) {
        Route::get('/humans.txt', fn() => $txtResponse('humans_txt'));
    }
    if(Settings::getOrDefault('robots_txt.enabled')) {
        Route::get('/robots.txt', fn() => $txtResponse('robots_txt'));
    }
    if(Settings::getOrDefault('security_txt.enabled')) {
        Route::get('/security.txt', fn() => $txtResponse('security_txt'));
        Route::get('/.well-known/security.txt', fn() => $txtResponse('security_txt'));
    }
    $settings = Settings::instance();
    if(Settings::getOrDefault('favicon.enabled') && $settings->app_favicon) {
        Route::get('favicon.ico', function() use ($settings) {
            $outputPath = $settings->app_favicon->getLocalPath();
            return response()->file($outputPath, [ 'Content-Type'=> 'image/x-icon' ]);
        });      
    }
});
