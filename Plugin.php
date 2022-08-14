<?php namespace Winter\SEO;

use Backend;
use Backend\Models\UserRole;
use System\Classes\PluginBase;

/**
 * SEO Plugin Information File
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
        return []; // Remove this line to activate

        return [
            'winter.seo.some_permission' => [
                'tab' => 'winter.seo::lang.plugin.name',
                'label' => 'winter.seo::lang.permissions.some_permission',
                'roles' => [UserRole::CODE_DEVELOPER, UserRole::CODE_PUBLISHER],
            ],
        ];
    }

    /**
     * Registers backend navigation items for this plugin.
     */
    public function registerNavigation(): array
    {
        return []; // Remove this line to activate

        return [
            'seo' => [
                'label'       => 'winter.seo::lang.plugin.name',
                'url'         => Backend::url('winter/seo/mycontroller'),
                'icon'        => 'icon-leaf',
                'permissions' => ['winter.seo.*'],
                'order'       => 500,
            ],
        ];
    }
}
