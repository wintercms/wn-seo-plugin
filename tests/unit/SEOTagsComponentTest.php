<?php

namespace Winter\SEO\Tests\Unit;

use System\Tests\Bootstrap\TestCase;
use Winter\SEO\Classes\Link;
use Winter\SEO\Classes\Meta;
use Winter\SEO\Components\SEOTags;

class SEOTagsComponentTest extends TestCase
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
     * Render the meta tags for a page that configures nothing, built without the
     * constructor so that no CMS request is needed. og:image:alt is preset so that
     * the alt-text branch, which dereferences the controller, is not reached.
     */
    protected function renderMetaTags(): void
    {
        $component = (new \ReflectionClass(SEOTags::class))->newInstanceWithoutConstructor();

        $page = new \stdClass;
        foreach (['meta_title', 'meta_description', 'meta_image', 'meta_nofollow', 'paginatePrev', 'paginateNext'] as $property) {
            $page->{$property} = '';
        }

        $this->setProtectedProperty($component, 'page', $page);

        $component->getMetaTags();
    }

    /**
     * The image type is resolved from the image URL, including when the width and
     * height are supplied through the SeoableModel mappings and the image is
     * therefore not resized
     */
    public function testOgImageTypeIsResolvedWhenDimensionsAreProvided()
    {
        Meta::set('og:image', 'https://example.com/uploads/image.jpg');
        Meta::set('og:image:width', '1200');
        Meta::set('og:image:height', '630');
        Meta::set('og:image:alt', 'An image');

        $this->renderMetaTags();

        $this->assertSame('image/jpeg', Meta::get('og:image:type'));
    }

    /**
     * The image is resized when the width or the height is missing, and the image
     * type is resolved from the resized URL
     */
    public function testOgImageTypeIsResolvedWhenTheImageIsResized()
    {
        Meta::set('og:image', 'https://example.com/uploads/image.jpg');
        Meta::set('og:image:alt', 'An image');

        $this->renderMetaTags();

        $this->assertSame('image/jpeg', Meta::get('og:image:type'));
    }
}