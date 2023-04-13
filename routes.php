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
        $contents = Settings::get($key);
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
    if(Settings::getOrDefault('favicon.enabled')) {
        Route::get('favicon.ico', function() {
            $faviconPath = media_path(Settings::get('favicon.path'));
            try {
                $favicon = new ImageResizer($faviconPath, 16, 16, ['mode'=>'crop']);
                $favicon->resize();
                $outputPath = storage_path('app/'.$favicon->getPathToResizedImage());
            } catch(Exception $e) {
                $outputPath = base_path($faviconPath);
            }
            return response()->file($outputPath, [ 'Content-Type'=> 'image/x-icon' ]);
        });      
    }
});
