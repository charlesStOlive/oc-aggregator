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
        $class = post('model');

        $ds = new DataSource($class, 'class');
        //Uniquement pour l'aggrégation manuel d'un model on vide tout puisque tout sera recalculé.
        Aggeable::where('aggeable_type', $ds->code)->where('aggeable_id', $modelId)->delete();

        $aggConfig = $ds->getAggConfig();
        $aggConfig->launchOne($modelId);
    }

    public function onAggregateAll()
    {

        $class = post('model');
        //trace_log($class);
        $ds = new DataSource($class, 'class');
        $aggConfig = $ds->getAggConfig();

        $models = $class::get(['id']);
        $modelsChunk = $models->chunk($aggConfig->chunk);

        $today = Carbon::now();

        $aggLog = AggeableLog::create([
            'taken_at' => $today,
            'data_source' => $ds->code,
            'parts' => $modelsChunk->count(),
        ]);

        foreach ($modelsChunk as $models) {
            $datas = [
                'class' => $class,
                'ids' => $models->pluck('id')->toArray(),
                'logId' => $aggLog->id,
            ];
            //trace_log($datas);
            //trace_log('Lanvement queue');
            $jobId = \Queue::push('\Waka\Agg\Classes\AggQueue@fire', $datas);
            \Event::fire('job.create.agg', [$jobId, 'Import lot agrégation ']);
        }
    }

}
