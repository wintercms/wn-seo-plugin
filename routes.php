<?php
use Winter\SEO\Models\Settings;
use Winter\Storm\Database\Attach\Resizer;
use Cms\Classes\CmsController;
use File as FileManager;

CmsController::extend(function($controller) {
  if(Settings::getOrDefault('global_minify_html')) {
    $controller->middleware('Winter\SEO\Middleware\CompressHTML');
  }
});

Event::listen('backend.page.beforeDisplay', function($controller, $action, $params) {
    $controller->addJs('/plugins/winter/seo/assets/counter.js');
});

Event::listen('system.beforeRoute', function () {
    $notFoundError = function () {
        return Response::make((new \Cms\Classes\Controller())->run('404'), 404);
    };

    $txtResponse = function ($key) {
        $contents = Settings::get($key);
        if (empty($contents)) {
            return $notFoundError();
        }
        return Response::make($contents, 200, ['Content-Type' => 'text/plain']);
    };

    if(Settings::getOrDefault('enable_favicon')) {
        Route::get('favicon.ico', function() use ($notFoundError) {
            $favicon = Settings::get('favicon');
            $faviconPath = base_path().media_path($favicon);
            $outputPath = base_path().media_path('favicon/' . basename($favicon));
            if(!strlen($favicon) || !file_exists($faviconPath)) {
                return $notFoundError();
            }
            try {
                if (!file_exists($outputPath)) {
                    Resizer::open($faviconPath)->resize(16, 16)->save($outputPath);
                }
            } catch(Exception $e) {
                $outputPath = $faviconPath;
            }
            return response()->file($outputPath, [ 'Content-Type'=> 'image/x-icon' ]);
        });      
    }
    if(Settings::getOrDefault('enable_humans_txt')) {
        Route::get('/humans.txt', fn() => $txtResponse('humans_txt'));
    }
    if(Settings::getOrDefault('enable_robots_txt')) {
        Route::get('/robots.txt', fn() => $txtResponse('robots_txt'));
    }
    if(Settings::getOrDefault('enable_security_txt')) {
        Route::get('/security.txt', fn() => $txtResponse('security_txt'));
        Route::get('/.well-known/security.txt', fn() => $txtResponse('security_txt'));
    }
});
