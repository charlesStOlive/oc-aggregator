<?php namespace Waka\Agg\Controllers;

use BackendMenu;
use Backend\Classes\Controller;

/**
 * Ag Months Back-end Controller
 */
class AgMonths extends Controller
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
        BackendMenu::setContext('Waka.Agg', 'agg', 'side-menu-agmonths');
    }

    // public function onAggregateOne($modelId = null)
    // {
    //     if (!$modelId) {
    //         $modelId = post('modelId');
    //     }
    //     $agg = AgMonth::find($modelId);
    //     $aggClass = $agg->data_source->agg_class;
    //     \Queue::push($aggClass . '@fire', ['class' => 'Waka\Agg\Models\AgMonth', 'modelId' => $modelId]);
    //     // $aggClass = new $aggClass;
    //     // $aggClass->fire(null, ['class' => 'Waka\Agg\Models\AgMonth', 'modelId' => $modelId]);
    // }

    // public function onAggregateChecked()
    // {
    //     trace_log(post('checked'));
    //     $modelIds = post('checked');
    //     foreach ($modelIds as $modelId) {
    //         $this->onAggregateOne($modelId);
    //     }
    // }
}
