<?php namespace Winter\SEO;

use Backend\Models\UserRole;
use Config;
use Event;
use System\Classes\PluginBase;
use Winter\SEO\Behaviors\SeoableModel;
use Winter\SEO\Classes\Link;
use Winter\SEO\Classes\Meta;
use Winter\SEO\Models\Settings;
use Yaml;

/**
 * SEO Plugin Information File
 * @TODO:
 * - Support for auto generating
 *     - humans.txt prepopulate from project composer.json?
 *     - Twig parsing in .txt file definitions
 *     - When adding support for twig parsing also add heavy caching by default for parsed results
 * - Support for creating meta data in separate related model in the SeoableModel behavior
 *     - check for extended with in the form event listener
 *     - Support templated strings for meta data managed by plugin (i.e. {{ record.name }} - {{ record.category }})
 * - Dev tools console (like Winter.Debugbar) that shows SEO information for the current page
 *     - Meta tags
 *     - HTML Validation
 *     - Previews (google, bing, facebook, twitter)
 *     - Sitemap check
 *     - accessibility and performance checks
 *     - general checklist of SEO best practices
 *     - content analysis (maybe integration with OpenAI API?)
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
 * - Support for "Social Media" quick connects (FB / Twitter IDs / accounts) (maybe just via global tags)
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
        $this->registerSeoableModels();
        $this->extendBackendForms();

        $this->extendPagesForms();
        $this->populateGlobalTags();
    }

    /**
     * Attaches the Seoable behavior to the configured models
     */
    protected function registerSeoableModels(): void
    {
        $modelsToTrack = Config::get('winter.seo::seoableModels', []);
        foreach ($modelsToTrack as $class => $config) {
            if (is_array($config)) {
                $modelClass = $class;
            } else {
                $modelClass = $config;
                $config = [];
            }

            if (!class_exists($modelClass)) {
                continue;
            }

            $modelClass::extend(function ($model) use ($config) {
                if (!empty($config['data_column'])) {
                    $model->addDynamicProperty('seoableDataColumn', $config['data_column']);
                }
                if (isset($config['meta_from'])) {
                    $model->addDynamicProperty('seoableMetaFrom', $config['meta_from']);
                }
                if (isset($config['link_from'])) {
                    $model->addDynamicProperty('seoableLinkFrom', $config['link_from']);
                }

                $model->extendClassWith(SeoableModel::class);
            });
        }
    }

    /**
     * Extends the backend forms to add the SEO tab to models
     * implementing the SeoableModel behavior
     */
    protected function extendBackendForms(): void
    {
        if ($this->app->runningInBackend()) {
            // Add the SEO fields to models implementing SeoableModel
            Event::listen('backend.form.extendFieldsBefore', function (\Backend\Widgets\Form $widget) {
                if (
                    $widget->isNested
                    || (
                        !method_exists($widget->model, 'isClassExtendedWith')
                        || (
                            !$widget->model->isClassExtendedWith(SeoableModel::class)
                            || !$widget->model->seoableInjectSeoFields
                        )
                    )
                ) {
                    return;
                }

                $tabsFields = $widget->tabs['fields'] ?? [];
                $secondaryTabsFields = $widget->secondaryTabs['fields'] ?? [];
                $location = (count($tabsFields) > count($secondaryTabsFields)) ? 'tabs' : 'secondaryTabs';

                $seoForm = Yaml::parseFile(plugins_path('winter/seo/models/seodata/fields.yaml'));
                $tab = 'winter.seo::lang.models.meta.label';

                $prefix = $widget->model->seoableDataColumn . '[seo_data]';

                $fields = [];
                foreach ($seoForm['fields'] as $name => $config) {
                    $config['tab'] = $tab;
                    $fields["{$prefix}[{$name}]"] = $config;
                }

                $widget->{$location}['paneCssClass'][$tab] = 'padded-pane';
                $widget->{$location}['icons'][$tab] = 'icon-magnifying-glass';

                $widget->{$location}['fields'] = array_merge($widget->{$location}['fields'], $fields);
            });
        }
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

        Event::listen('backend.form.extendFieldsBefore', function (\Backend\Widgets\Form $widget) use ($controllerModels) {
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

            unset($widget->tabs['fields']["{$prefix}title]"]);
            unset($widget->tabs['fields']["{$prefix}description]"]);

            $form = Yaml::parseFile(plugins_path('winter/seo/models/seodata/fields.halcyon.yaml'));
            $tab = 'winter.seo::lang.models.meta.label';
            $halcyonFields = [];
            foreach ($form['fields'] as $name => $config) {
                $config['tab'] = $tab;
                $halcyonFields["{$prefix}{$name}]"] = $config;
            }

            $widget->tabs['paneCssClass'][$tab] = 'padded-pane';
            $widget->tabs['icons'][$tab] = 'icon-magnifying-glass';

            $widget->tabs['fields'] = array_merge($widget->tabs['fields'], $halcyonFields);
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

        // Add the Winter CMS generator tag
        Meta::set('generator', 'Winter CMS');
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
