<?php namespace Waka\Agg\Behaviors;

use Backend\Classes\ControllerBehavior;
use Carbon\Carbon;
use Waka\Utils\Classes\DataSource;
use Queue;
use Waka\Agg\Models\AggeableLog;

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

        $today = Carbon::now();

        $aggLog = AggeableLog::create([
            'taken_at' =>$today,
            'data_source_id' =>$ds->id,
            'parts' => $modelsChunk->count(),
        ]);

        foreach($modelsChunk as $models) {
            $datas = [
                'class' =>$class,
                'ids' => $models->pluck('id')->toArray(),
                'logId' => $aggLog->id,
            ];
            $jobId = \Queue::push('\Waka\Agg\Classes\AggQueue@fire', $datas);
            \Event::fire('job.create.agg', [$jobId, 'Import lot agrégation ']);
        }
    }

    
}
