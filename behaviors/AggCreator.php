<?php namespace Waka\Agg\Behaviors;

use Backend\Classes\ControllerBehavior;
use Carbon\Carbon;
use Queue;
use Waka\Agg\Models\Aggeable;
use Waka\Agg\Models\AggeableLog;
use Waka\Utils\Classes\DataSource;

class AggCreator extends ControllerBehavior
{
    protected $controller;

    public function __construct($controller)
    {
        parent::__construct($controller);
    }

    public function onAggregate()
    {
        $modelId = post('modelId');
        $class = post('modelClass');

        $ds = new DataSource($class, 'class');
        //Uniquement pour l'aggrégation manuel d'un model on vide tout puisque tout sera recalculé.
        Aggeable::where('aggeable_type', $ds->code)->where('aggeable_id', $modelId)->delete();

        $aggConfig = $ds->getAggConfig();
        $aggConfig->launchOne($modelId);
    }

    public function onAggregateAll()
    {

        $class = post('modelClass');

        $job = new \Waka\Agg\Jobs\AggJob($class);
        $jobManager = \App::make('Waka\Wakajob\Classes\JobManager');
        $jobManager->dispatch($job, "Aggrégations");
        $this->vars['jobId'] = $job->jobId;
        return $this->makePartial('$/waka/wakajob/controllers/jobs/_confirm_popup.htm');
    }
}
