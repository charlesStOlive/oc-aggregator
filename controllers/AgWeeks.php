<?php namespace Waka\Agg\Controllers;

use BackendMenu;
use Backend\Classes\Controller;

/**
 * Ag Weeks Back-end Controller
 */
class AgWeeks extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController',
        'Waka.Agg.Behaviors.Aggregate',
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Waka.Agg', 'agg', 'side-menu-agweeks');
    }
}
