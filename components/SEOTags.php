<?php

namespace Winter\SEO\Components;

use Backend\Models\BrandSetting;
use Cms\Classes\ComponentBase;
use Config;
use Lang;
use Symfony\Component\Mime\MimeTypes;
use System\Classes\ImageResizer;
use System\Classes\MediaLibrary;
use Url;
use Winter\SEO\Classes\Link;
use Winter\SEO\Classes\Meta;

class SEOTags extends ComponentBase
{
    /**
     * Gets the details for the component
     */
    public function componentDetails()
    {
        return [
            'name'        => 'SEOTags Component',
            'description' => 'No description provided yet...'
        ];
    }

    /**
     * Processes the meta tags for CMS pages and Winter.Pages static pages
     */
    protected function processPageMeta()
    {
        // $this['page_title'] = $this->page->title ?? Meta::get('og:title') ?? '';
        // $this['app_name'] = BrandSetting::get('app_name');

        // Set the cannonical URL
        if (empty(Link::get('canonical'))) {
            Link::set('canonical', Url::current());
        }

        // Parse the meta_image as a media library image
        if (!empty($this->page->meta_image)) {
            $this->page->meta_image = MediaLibrary::url($this->page->meta_image);
        }

        // Handle the nofollow meta property being set
        if (!empty($this->page->meta_nofollow)) {
            Link::set('robots', 'nofollow');
        }

        // Set the meta tags based on the current page if not set
        $metaMap = [
            Meta::class => [
                'og:title' => 'meta_title',
                'og:description' => 'meta_description',
                'og:image' => 'meta_image',
            ],
            Link::class => [
                'prev' => 'paginatePrev',
                'next' => 'paginateNext',
            ],
        ];
        foreach ($metaMap as $class => $map) {
            foreach ($map as $name => $pageProp) {
                if (
                    empty($class::get($name))
                    && !empty($this->page->{$pageProp})
                ) {
                    $class::set($name, $this->page->{$pageProp});
                }
            }
        }
    }

    /**
     * Processes the og:image Meta tag
     */
    protected function processOgImage(): void
    {
        $image = Meta::get('og:image') ?? Config::get('winter.seo::default_social_image', null);

        if ($image) {
            // Tell Twitter to display as a summary card with an image if we have an image defined
            if (empty(Meta::get('twitter:card'))) {
                Meta::set('twitter:card', 'summary_large_image');
            }

            // Ensure the image dimensions are set
            if (
                empty(Meta::get('og:image:width'))
                || empty(Meta::get('og:image:height'))
            ) {
                Meta::set('og:image:width', Config::get('winter.seo::social_image.default_width', 1200));
                Meta::set('og:image:height', Config::get('winter.seo::social_image.default_height', 630));
                $imageUrl = Url::to(
                    ImageResizer::filterGetUrl(
                        $image,
                        Meta::get('og:image:width'),
                        Meta::get('og:image:height'),
                        [
                            'mode' => 'crop',
                        ],
                    )
                );
                Meta::set('og:image', $imageUrl);
            }

            // Ensure the image type is set
            if (empty(Meta::get('og:image:type'))) {
                $mimeTypes = (new MimeTypes())->getMimeTypes(
                    pathinfo(
                        parse_url($imageUrl, PHP_URL_PATH),
                        PATHINFO_EXTENSION
                    )
                ) ?? [];
                if (count($mimeTypes)) {
                    Meta::set('og:image:type', $mimeTypes[0]);
                }
            }

            // Ensure the image alt text is set
            if (empty(Meta::get('og:image:alt'))) {
                Meta::set('og:image:alt', Lang::get('winter.seo::lang.meta.og:image:alt', [
                    'title' => Meta::get('og:title') ?? Meta::get('title') ?? $this->controller->getPage()['title'] ?? '',
                    'app_name' => BrandSetting::get('app_name'),
                ]));
            }
        }
    }

    /**
     * Processes the og:description / description meta tags
     */
    protected function processDescription(): void
    {
        if (!empty(Meta::get('description')) && empty(Meta::get('og:description'))) {
            Meta::set('og:description', Meta::get('description'));
        } elseif (!empty(Meta::get('og:description')) && empty(Meta::get('description'))) {
            Meta::set('description', Meta::get('og:description'));
        }
    }

    /**
     * Processes the og:url meta tag, defaulting to the canonical URL or the current page URL
     */
    protected function processOgUrl(): void
    {
        if (empty(Meta::get('og:url'))) {
            Meta::set('og:url', Link::get('canonical') ?? Url::current());
        }
    }

    /**
     * Processes the og:type meta tag, defaulting to "website"
     */
    protected function processOgType(): void
    {
        if (empty(Meta::get('og:type'))) {
            Meta::set('og:type', 'website');
        }
    }

    /**
     * Processes the og:site_name meta tag, defaulting to "website"
     */
    protected function processOgSiteName(): void
    {
        if (empty(Meta::get('og:site_name'))) {
            Meta::set('og:site_name', BrandSetting::get('app_name'));
        }
    }

    public function getMetaTags(): array
    {
        $this->processPageMeta();
        $this->processOgImage();
        $this->processDescription();
        $this->processOgUrl();
        $this->processOgType();
        $this->processOgSiteName();

        return Meta::all();
    }

    public function getLinkTags(): array
    {
        return Link::all();
    }

        // dd(Meta::all(), Link::all(), __LINE__, __FILE__);


        // Meta::set('og:title', $meta['title']);
        // Meta::set('og:description', $meta['description']);
        // Meta::set('og:image', \System\Classes\MediaLibrary::url($meta['image']));
        // Link::set('canonical', $meta['canonical_url']);


// {# Pagination Links #}
// {% if meta.pagination_prev_url %}
//     <link rel="prev" href="{{ meta.pagination_prev_url }}">
// {% endif %}
// {% if meta.pagination_next_url %}
//     <link rel="next" href="{{ meta.pagination_next_url }}">
// {% endif %}


// {#
//     URL Type
//     Allowed / Relevant:
//         - website
//         - article
//         - profile
//         - book
// #}
// <meta name="og:type" content="{{ meta.type | default('website') }}" />
}
