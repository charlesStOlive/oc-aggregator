<?php namespace Waka\Agg;

use System\Classes\PluginBase;

/**
 * agg Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name' => 'agg',
            'description' => 'No description provided yet...',
            'author' => 'waka',
            'icon' => 'icon-leaf',
        ];
    }

    /**
     * Register method, called when the plugin is first registered.
     *
     * @return void
     */
    public function register()
    {

    }

    /**
     * Boot method, called right before the request route.
     *
     * @return array
     */
    public function boot()
    {

    }

    /**
     * Registers any front-end components implemented in this plugin.
     *
     * @return array
     */
    public function registerComponents()
    {
        return []; // Remove this line to activate

        return [
            'Waka\Agg\Components\MyComponent' => 'myComponent',
        ];
    }

    /**
     * Registers any back-end permissions used by this plugin.
     *
     * @return array
     */
    public function registerPermissions()
    {
        return [
            'waka.agg.admin.super' => [
                'tab' => 'Waka - Aggrégateur',
                'label' => 'Super Administrateur des agrégations',
            ],
            'waka.agg.admin.base' => [
                'tab' => 'Waka - Aggrégateur',
                'label' => 'Administrateur des agrégations',
            ],
            'waka.agg.user' => [
                'tab' => 'Waka - Aggrégateur',
                'label' => 'Utilisateur des agrégations',
            ],
        ];
    }

    /**
     * Registers back-end navigation items for this plugin.
     *
     * @return array
     */
    public function registerSettings()
    {
        return [
            'AggeableLogs' => [
                'label' => \Lang::get('waka.agg::lang.menu.aggeable_logs'),
                'description' => \Lang::get('waka.agg::lang.menu.aggeable_logs_description'),
                'category' => \Lang::get('waka.utils::lang.menu.settings_category'),
                'icon' => 'icon-calculator',
                'url' => \Backend::url('waka/agg/aggeablelogs'),
                'permissions' => ['waka.agg.admin.*'],
                'order' => 20,
            ],
            // 'agg_settings' => [
            //     'label' => 'Test Settings',
            //     'description' => 'Test settings descr',
            //     'class' => 'Waka\Agg\Models\Settings',
            // ],
        ];
    }

    /**
     * Registers back-end navigation items for this plugin.
     *
     * @return array
     */
    public function registerNavigation()
    {

        return [];
    }
}
