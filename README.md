# SEO Plugin

Easily handle Search Engine Optimization in your Winter CMS projects. Inspired by https://github.com/bennothommo/wn-meta-plugin.

Future plans including support for easily generating structured data and automatically attaching SEO meta fields to CMS pages, Winter.Pages pages, & generically to any Winter CMS model. Check the TODO list in Plugin.php for more planned features.

## Installation

```bash
composer require wintercms/wn-seo-plugin
```

Then add the `[seoTags]` component to your `<head>` in your theme, ideally right after the standard encoding and responsiveness tags.

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
