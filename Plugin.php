<?php namespace Waka\Agg;

use Backend;
use Lang;
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
        $this->registerConsoleCommand('waka.agg', 'Waka\Agg\Console\CreateAggTable');
    }

    /**
     * Boot method, called right before the request route.
     *
     * @return array
     */
    public function boot()
    {
        \Event::listen('agg.update', function ($model) {
            trace_log("reception de l'event");
            // $uniqueAgg = new \Waka\Agg\Classes\UniqueAgg();
            // $uniqueAgg->manual($model);

            \Queue::push('Waka\Agg\Classes\UniqueAgg@fire', ['classAgg' => get_class($model), 'modelAggId' => $model->id]);
        });

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
    /**
     * Registers back-end navigation items for this plugin.
     *
     * @return array
     */
    public function registerNavigation()
    {
        return [
            'agg' => [
                'label' => Lang::get('waka.agg::lang.menu.title'),
                'url' => Backend::url('waka/agg/agmonths'),
                'icon' => 'icon-line-chart',
                'permissions' => ['waka.agg.*'],
                'order' => 002,
                'sideMenu' => [
                    'side-menu-years' => [
                        'label' => Lang::get('waka.agg::lang.menu.agyears'),
                        'icon' => 'icon-hourglass',
                        'url' => Backend::url('waka/agg/agyears'),
                    ],
                    'side-menu-months' => [
                        'label' => Lang::get('waka.agg::lang.menu.agmonths'),
                        'icon' => 'icon-hourglass-end',
                        'url' => Backend::url('waka/agg/agmonths'),
                    ],
                    'side-menu-week' => [
                        'label' => Lang::get('waka.agg::lang.menu.agweeks'),
                        'icon' => 'icon-hourglass-o',
                        'url' => Backend::url('waka/agg/agweeks'),
                    ],
                ],
            ],
        ];
    }
}
