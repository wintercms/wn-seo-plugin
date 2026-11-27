<?php

namespace Winter\SEO\Tests\Unit;

use System\Tests\Bootstrap\TestCase;
use Winter\SEO\Classes\Link;
use Winter\SEO\Classes\Meta;
use Winter\SEO\Components\SEOTags;

class SEOTagsRobotsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Meta::refresh();
        Link::refresh();
    }

    protected function tearDown(): void
    {
        Meta::refresh();
        Link::refresh();

        parent::tearDown();
    }

    /**
     * Render the meta tags for a page whose nofollow setting is $nofollow, built
     * without the constructor so that no CMS request is needed. getMetaTags() calls
     * processPageMeta() first, which is the code under test here.
     */
    protected function renderMetaTagsForPage(bool $nofollow): void
    {
        $component = (new \ReflectionClass(SEOTags::class))->newInstanceWithoutConstructor();

        $page = new \stdClass;
        foreach (['meta_title', 'meta_description', 'meta_image', 'paginatePrev', 'paginateNext'] as $property) {
            $page->{$property} = '';
        }
        $page->meta_nofollow = $nofollow ? 1 : '';

        $this->setProtectedProperty($component, 'page', $page);

        $component->getMetaTags();
    }

    /**
     * A robots value configured site-wide is data the administrator entered. Setting
     * nofollow on one page must not delete it.
     */
    public function testNofollowDoesNotDiscardAConfiguredRobotsValue()
    {
        Link::set('robots', 'index, follow');

        $this->renderMetaTagsForPage(true);

        $this->assertSame('index, follow, nofollow', Link::get('robots'));
    }

    /**
     * With nothing configured, the output is unchanged: this is the default-install
     * case and it must be identical before and after the fix.
     */
    public function testNofollowIsUnchangedWhenNothingIsConfigured()
    {
        $this->renderMetaTagsForPage(true);

        $this->assertSame('nofollow', Link::get('robots'));
    }

    /**
     * A page that does not set nofollow leaves a configured robots value alone
     */
    public function testConfiguredRobotsIsUntouchedWithoutNofollow()
    {
        Link::set('robots', 'index, follow');

        $this->renderMetaTagsForPage(false);

        $this->assertSame('index, follow', Link::get('robots'));
    }
}