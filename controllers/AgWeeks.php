<?php namespace Waka\Agg\Controllers;

use BackendMenu;
use Backend\Classes\Controller;

/**
 * Ag Weeks Back-end Controller
 */
class AgWeeks extends Aggregations
{
    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Waka.Agg', 'agg', 'side-menu-agweeks');
    }
}
