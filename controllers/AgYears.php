<?php namespace Waka\Agg\Controllers;

use BackendMenu;
use Backend\Classes\Controller;

/**
 * Ag Years Back-end Controller
 */
class AgYears extends Aggregations
{
    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Waka.Agg', 'agg', 'side-menu-agyears');
    }
}
