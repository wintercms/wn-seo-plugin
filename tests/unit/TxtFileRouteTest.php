<?php

namespace Winter\SEO\Tests\Unit;

use Config;
use Winter\SEO\Models\Settings;
use System\Tests\Bootstrap\PluginTestCase;

/**
 * Tests for the txt file routes served by the plugin (humans.txt, robots.txt,
 * security.txt and the llms.txt added for
 * https://github.com/winter/cms/wn-seo-plugin/issues/17).
 *
 * routes.php is required once when the plugin is loaded by PluginTestCase, so
 * the routes are already in the collection in setUp(); this test only needs to
 * find and run the closure that was registered.
 */
class TxtFileRouteTest extends PluginTestCase
{
    /**
     * Returns the route registered for the given URI, or null when none exists.
     */
    protected function findRoute(string $uri)
    {
        return collect($this->app['router']->getRoutes()->getRoutes())
            ->first(function ($route) use ($uri) {
                return $route->uri() === $uri;
            });
    }

    /**
     * Runs the txt route's closure and returns the Response it produced.
     */
    protected function runTxtRoute(string $uri)
    {
        $route = $this->findRoute($uri);

        $this->assertNotNull(
            $route,
            'no route registered for ' . $uri
        );

        // A route pulled out of the collection has no container and no
        // parameters: runCallable() resolves what it needs from the container and
        // hands the bound request to the closure. Binding both is what
        // Router::findRoute() would have done.
        $route->setContainer($this->app);
        $route->bind(request());

        return $route->run();
    }

    public function testLlmsTxtRouteIsRegistered()
    {
        $this->assertNotNull(
            $this->findRoute('llms.txt'),
            'the /llms.txt route was not registered'
        );
    }

    public function testLlmsTxtRouteServesTheConfiguredContent()
    {
        Settings::set('llms_txt', "# Winter SEO\n\n> LLM-friendly overview.");

        $response = $this->runTxtRoute('llms.txt');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('text/plain', $response->headers->get('Content-Type'));
        $this->assertStringContainsString(
            '# Winter SEO',
            $response->getContent()
        );
    }

    public function testLlmsTxtRouteIsCachedAcrossRequests()
    {
        // Caching only kicks in when debug is off (the route forgets the cache
        // entry on every request while debug is on, which is the development
        // convenience). Turn it off so the second call hits the cache.
        Config::set('app.debug', false);

        Settings::set('llms_txt', 'first');

        $this->assertSame('first', $this->runTxtRoute('llms.txt')->getContent());

        // The same cached output is returned even though the settings changed,
        // proving the cache is in effect.
        Settings::set('llms_txt', 'second');

        $this->assertSame('first', $this->runTxtRoute('llms.txt')->getContent());
    }

    public function testLlmsTxtRouteIsInvalidatedInDebugMode()
    {
        Config::set('app.debug', true);

        Settings::set('llms_txt', 'first');

        $this->assertSame('first', $this->runTxtRoute('llms.txt')->getContent());

        Settings::set('llms_txt', 'second');

        $this->assertSame('second', $this->runTxtRoute('llms.txt')->getContent());
    }

    /**
     * The existing txt routes must remain registered alongside the new one.
     */
    public function testExistingTxtRoutesAreStillRegistered()
    {
        foreach (['humans.txt', 'robots.txt', 'security.txt'] as $uri) {
            $this->assertNotNull(
                $this->findRoute($uri),
                'the /' . $uri . ' route is no longer registered'
            );
        }
    }
}
