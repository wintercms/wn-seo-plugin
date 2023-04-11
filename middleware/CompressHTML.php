<?php namespace Winter\SEO\Middleware;

use Closure;
use Cache;
use Illuminate\Http\Request;

class CompressHTML {
    public function handle (Request $request, Closure $next) 
    {
        // Key to store HTML in cache
        $cacheKey = 'winter.seo.minified'.$request->getRequestUri();
        // Time to live in seconds
        $cacheTTL = 3600; 
        // Get cached HTML or compress and save if cache not exists
        $content = Cache::remember($cacheKey, $cacheTTL, function() use ($request, $next) {
          return $this->compress($next($request)->getContent());
        });
        return response($content);
    }

    protected function compress($buffer)
    {
        $replace = [
            // Remove HTML whitespaces
            "/\n([\S])/" => '$1',
            "/\r/" => '',
            "/\n/" => '',
            "/\t/" => '',
            "/ +/" => ' ',
            "/> +</" => '><',
            // Remove HTML comments
            '/<!--[^]><!\[](.*?)[^\]]-->/s' => '',
            // Remove unnecessary url parts
            '/https:/' => '',
            '/http:/' => '',
            // Replace attributes with short notation
            '/ method=("get"|get)/' => '',
            '/ disabled=[^ >]*(.*?)/' => ' disabled',
            '/ selected=[^ >]*(.*?)/' => ' selected',
        ];
        return preg_replace(array_keys($replace), array_values($replace), $buffer);
    }
}