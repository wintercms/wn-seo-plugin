<?php namespace Winter\SEO;

use Backend\Models\UserRole;
use Event;
use System\Classes\PluginBase;
use Winter\SEO\Classes\Link;
use Winter\SEO\Classes\Meta;
use Winter\SEO\Models\Settings;
use Yaml;

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

            $form = Yaml::parseFile(plugins_path('winter/seo/models/meta/fields.yaml'));
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
