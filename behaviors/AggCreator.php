<?php namespace Waka\Agg\Behaviors;

use Backend\Classes\ControllerBehavior;
use Carbon\Carbon;
use Waka\Utils\Classes\DataSource;
use Queue;

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
        $class = post('model');

        $ds = new DataSource($class, 'class');
        $aggConfig = $ds->getAggConfig();
        $aggConfig->launchOne($modelId);
    }

    public function onAggregateAll()
    {
        
        $class = post('model');
        $ds = new DataSource($class, 'class');
        $aggConfig = $ds->getAggConfig();

        $models = $class::get(['id']);
        $modelsChunk = $models->chunk($aggConfig->chunk);

        foreach($modelsChunk as $models) {
            $datas = [
                'class' =>$class,
                'ids' => $models->pluck('id')->toArray(),
            ];
            $jobId = \Queue::push('\Waka\Agg\Classes\AggQueue@fire', $datas);
            \Event::fire('job.create.agg', [$jobId, 'Import lot agrégation ']);
        }
    }

    
}
