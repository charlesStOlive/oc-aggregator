<?php namespace Waka\Agg\Controllers;

use BackendMenu;
use Backend\Classes\Controller;

/**
 * Ag Months Back-end Controller
 */
class AgMonths extends Aggregations
{
    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Waka.Agg', 'agg', 'side-menu-agmonths');
    }

}
