# SEO Plugin

Easily handle Search Engine Optimization in your Winter CMS projects. Inspired by https://github.com/bennothommo/wn-meta-plugin.

Future plans including support for easily generating structured data and automatically attaching SEO meta fields to CMS pages, Winter.Pages pages, & generically to any Winter CMS model. Check the TODO list in Plugin.php for more planned features.

## Installation

```bash
composer require winter/wn-seo-plugin
```

Then add the `[seoTags]` component to your `<head>` in your theme, ideally right after the standard encoding and responsiveness tags.

### Suggested implementation:

**Layout or header partial**: 
```twig
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        {% partial "meta/seo" %}
    </head>
```

**`partials/meta/seo.htm`:**
```twig
[seoTags]
==
<?php
use Backend\Models\BrandSetting;
use System\Classes\MediaLibrary;
use Winter\SEO\Classes\Link;
use Winter\SEO\Classes\Meta;
function onStart()
{
    $this['page_title'] = $this->page->title ?? Meta::get('og:title') ?? '';
    $this['app_name'] = BrandSetting::get('app_name');

    // Set the cannonical URL
    Link::set('canonical', \URL::current());

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
            if (!empty($this->page->{$pageProp}) && empty($class::get($name))) {
                $class::set($name, $this->page->{$pageProp});
            }
        }
    }

    $this['raw_title'] = Meta::get('title');
}
?>
==
<title>
    {%- placeholder page_title default %}
        {%- if raw_title %}{{ raw_title | striptags }}{% elseif page_title %}{{ page_title | striptags }} | {{ app_name }}{% else %}{{ app_name }}{% endif -%}
    {% endplaceholder -%}
</title>
{% component seoTags %}
```

## Configuration

Configuration for this plugin is handled through a [configuration file](https://wintercms.com/docs/plugin/settings#file-configuration). In order to modify the configuration values and get started you can copy the `plugins/winter/seo/config/config.php` file to `config/winter/seo/config.php` and make your changes there.

## Usage

### Meta Tags

Use the `Meta` class to add `<meta>` tags that will be rendered by the `[seoTags]` component. Examples:

```php
use Winter\SEO\Classes\Meta;

// Adds <meta name="og:type" content="article">
Meta::set('og:type', 'article');

// Appends a meta tag to the collection; allowing for full control of the
// attributes used as well as preventing it from being overridden and / or
// enabling multiple tags with the same name to be added.
Meta::append([
    'name' => 'og:type',
    'content' => 'article',
    'example_attribute' => 'the_cake_is_a_lie',
]);

// Overrides `og:type` because it was set later in the request
Meta::set('og:type', 'article');

// Retreive a specific meta tag by its name
Meta::get('og:type');

// Retrieve all meta tags currently set in this request
Meta::all()

// Clear all previously set meta tags and start fresh from this point on in the request
Meta::refresh();
```

### Link Tags

Use the `Link` class to add `<link>` tags that will be rendered by the `[seoTags]` component. Examples:

```php
use Winter\SEO\Classes\Link;

// Adds <link rel="base_url" rel="https://example.com">
Link::set('base_url', 'https://example.com');

// Appends a link tag to the collection; allowing for full control of the
// attributes used as well as preventing it from being overridden and / or
// enabling multiple tags with the same name to be added.
Link::append([
    'rel' => 'preload',
    'href' => 'https://example.com/logo.png',
    'as' => 'image',
]);

// Overrides `base_url` because it was set later in the request
Link::set('base_url', url()->current());

// Retreive a specific link tag by its name
Link::get('base_url');

// Retrieve all link tags currently set in this request
Link::all()

// Clear all previously set link tags and start fresh from this point on in the request
Link::refresh();
```
