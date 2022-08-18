<?php namespace Winter\SEO;

use Backend\Models\UserRole;
use Event;
use System\Classes\PluginBase;
use Winter\SEO\Classes\Link;
use Winter\SEO\Classes\Meta;
use Winter\SEO\Models\Settings;
use Yaml;

/**
 * SEO Plugin Information File
 * @TODO:
 * - Support for robots.txt, humans.txt, and security.txt
 *     - humans.txt prepopulate from project composer.json?
 *     - Twig parsing in .txt file definitions
 *     - When adding support for twig parsing also add heavy caching by default for parsed results
 * - Support for Model Behavior that creates meta data in separate DB
 *     - check for extended with in the form event listener
 *     - Support templated strings for meta data managed by plugin (i.e. {{ record.name }} - {{ record.category }})
 * - Support for Winter.Translate
 * - Automatic extension of Winter.Blog
 * - Recommended plugins: Winter.Sitemap, Winter.Pages, Winter.Blog, Winter.Redirect
 * - Documentation
 * - All settings should be backed / default to config values for file based configuration
 * - Title prefix / suffix; includeTitle default true in component
 * - Comments / help text for all provided fields ideally with references on how they should be used
 * - Rename "Meta" tab to "SEO" tab
 * - Character limit on description field
 * - Full support for common robots settings via meta tags and site wide defaults
 * - Default addition of generator meta tag in config file
 * - Support for "Social Media" quick connects (FB / Twitter IDs / accounts) (maybe just via global tags)
 * - Support for more sources of og:image (i.e. manual URL, fileupload, mediafinder, etc)
 * - Favicon, webmanifest, browserconfig, apple touch icons, etc
 * - Previews of FB / Twitter / Google
 * - Support for structured data
 * - Winter.Redirect
 *     - Model behavior to setup automatic redirects when URL attribute changes
 * - Support for Winter.Builder pages
 * - Support for twig parsing of meta tag values?
 * - og:locale / other relevant locale flags, perhaps provide from Winter.Translate plugin? Or just from this plugin
 * - Contextual settings for adding records to the sitemap rather than having to add everything in the sitemap main section? Perhaps implement in Winter.Sitemap
 *
 * @Related:
 * - https://github.com/wintercms/wn-redirect-plugin
 * - https://github.com/wintercms/wn-sitemap-plugin
 * - https://github.com/wintercms/wn-pages-plugin
 * - https://github.com/wintercms/wn-blog-plugin
 * - https://github.com/wintercms/wn-translate-plugin
 *
 * @See also:
 * - https://github.com/bennothommo/wn-meta-plugin
 * - https://github.com/mjauvin/wn-mlsitemap-plugin
 * - https://github.com/josephcrowell/wn-sitemappretty-plugin
 * - https://github.com/studiobosco/wn-seo-extension
 * - https://github.com/WebVPF/wn-robots-plugin
 * - https://octobercms.com/plugin/renatio-seomanager
 * - https://octobercms.com/plugin/lovata-mightyseo
 * - https://octobercms.com/plugin/initbiz-seostorm
 * - https://octobercms.com/plugin/zen-robots
 * - https://octobercms.com/plugin/utopigs-seo
 * - https://octobercms.com/plugin/mohsin-txt
 * - https://octobercms.com/plugin/linkonoid-schemamarkup
 * - https://octobercms.com/plugin/vdlp-schemaorg
 * - https://octobercms.com/plugin/egalhtn-localbusiness
 * - https://octobercms.com/plugin/dynamedia-posts
 * - https://octobercms.com/plugin/eugene3993-seo
 * - https://octobercms.com/plugin/magiczne-seotweaker
 * - https://octobercms.com/plugin/eugene3993-seolight
 */
class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     */
    public function pluginDetails(): array
    {
        return [
            'name'        => 'winter.seo::lang.plugin.name',
            'description' => 'winter.seo::lang.plugin.description',
            'author'      => 'Winter',
            'icon'        => 'icon-leaf'
        ];
    }

    /**
     * Boot method, called right before the request route.
     */
    public function boot(): void
    {
        $this->extendPagesForms();
        $this->populateGlobalTags();
    }

    /**
     * Extends the Cms & Winter.Pages controllers with the SEO form fields
     */
    protected function extendPagesForms()
    {
        $controllerModels = [
            \Cms\Controllers\Index::class => [
                \Cms\Classes\Page::class,
            ],
            \Winter\Pages\Controllers\Index::class => [
                \Winter\Pages\Classes\Page::class,
            ],
        ];

        Event::listen('backend.form.extendFields', function (\Backend\Widgets\Form $widget) use ($controllerModels) {
            if ($widget->isNested) {
                return;
            }

            $controller = $widget->getController();
            $model = $widget->model;

            if (
                !isset($controllerModels[get_class($controller)])
                || !in_array(get_class($model), $controllerModels[get_class($controller)])
            ) {
                return;
            }

            if (get_class($model) == \Cms\Classes\Page::class) {
                $prefix = "settings[meta_";
            } else {
                $prefix = "viewBag[meta_";
            }

            $widget->removeField("{$prefix}title]");
            $widget->removeField("{$prefix}description]");

            $form = Yaml::parseFile(plugins_path('winter/seo/models/meta/fields.yaml'));
            $halcyonFields = [];
            foreach ($form['fields'] as $name => $config) {
                $config['tab'] = 'winter.seo::lang.models.meta.label';
                $halcyonFields["{$prefix}{$name}]"] = $config;
            }

            $widget->addTabFields($halcyonFields);
        });
    }

    /**
     * Populates the global tags with the values from the Settings
     * @TODO:
     *  - Test
     *  - add support for populating from the config file
     */
    protected function populateGlobalTags()
    {
        // @TODO: decide whether this should run as soon as possible to allow for overrides
        // or as late as possible with the empty check preventing it from overriding anything
        // and just being the absolute last fallback possible
        Event::listen('cms.page.beforeDisplay', function ($controller, $page) {
            $metaTags = Settings::get('meta_tags', []);
            $linkTags = Settings::get('link_tags', []);

            foreach ($metaTags as $data) {
                if (empty($data['name'] || empty($data['value']))) {
                    continue;
                }
                if (empty(Meta::get($data['name']))) {
                    Meta::set($data['name'], $data['value']);
                }
            }

            foreach ($linkTags as $data) {
                if (empty($data['rel'] || empty($data['href']))) {
                    continue;
                }
                if (empty(Link::get($data['rel']))) {
                    Link::set($data['rel'], $data['href']);
                }
            }
        });

    }

    /**
     * Registers any frontend components implemented in this plugin.
     */
    public function registerComponents(): array
    {
        return [
            \Winter\SEO\Components\SEOTags::class => 'seoTags',
        ];
    }

    /**
     * Registers any backend permissions used by this plugin.
     */
    public function registerPermissions(): array
    {
        return [
            'winter.seo.manage_meta' => [
                'tab' => 'winter.seo::lang.plugin.name',
                'label' => 'winter.seo::lang.permissions.manage_meta',
                'roles' => [UserRole::CODE_DEVELOPER, UserRole::CODE_PUBLISHER],
            ],
        ];
    }

    /**
     * Registers the settings provided by this plugin
     */
    public function registerSettings(): array
    {
        return [
            'seo' => [
                'label'       => 'winter.seo::lang.plugin.name',
                'description' => 'winter.seo::lang.plugin.description',
                'icon'        => 'icon-search',
                'class'       => \Winter\SEO\Models\Settings::class,
                'keywords'    => 'seo meta link verification',
                'permissions' => ['winter.seo.manage_meta'],
            ],
        ];
    }
}
