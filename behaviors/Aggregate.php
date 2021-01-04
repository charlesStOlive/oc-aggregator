<?php namespace Waka\Agg\Behaviors;

use Backend\Classes\ControllerBehavior;
use Carbon\Carbon;

class Aggregate extends ControllerBehavior
{
    protected $controller;

    public function __construct($controller)
    {
        parent::__construct($controller);
    }

    public function onAggregate()
    {
        $modelId = post('modelId');
        $model = post('model');

        $ds = new DataSource($model, 'class');
        $aggregateTyperClass = $ds->aggs;
        // $agg = $aggregateClass::find($modelId);

        // $aggClass = $agg->data_source->agg_class;
        // $aggClass = new $aggClass;
        // $aggClass->fire(null, ['class' => $aggregateClass, 'modelId' => $modelId]);
        return \Redirect::refresh();
    }

    public function onAggregateChecked()
    {
        $checked = post('checked');
        $aggregateClass = post('aggregateClass');
        foreach ($checked as $modelId) {
            $this->onAggregateOne($modelId, $aggregateClass);
        }
        \Flash::info("Le calcul des agrégation est en cours, vous pouvez verifier la progression des calculs dans REGLAGES->TACHES");
    }

    public function getPossibleDate($type)
    {
        $date = Carbon::now();
        if ($type == 'month') {
            return $date->copy()->format('Y-m-d');
        }
        if ($type == '3months') {
            return $date->copy()->subMonth(3)->format('Y-m-d');
        }
        if ($type == '6months') {
            return $date->copy()->subMonth(6)->format('Y-m-d');
        }
        if ($type == 'all') {
            return '1970-01-01';
        }
        return $date->copy()->format('Y-m-d');
    }

    public function onAggregateBehaviorPopupForm()
    {
        $this->controller->onAutoCreateAllAggregation();

        $aggregateClass = post('aggregateClass');
        $class = new $aggregateClass;
        $date = $this->getPossibleDate('month');
        $countMonth = $class::whereDate('end_at', '>=', $date)->count();
        $date3 = $this->getPossibleDate('3months');
        $count3Month = $class::whereDate('end_at', '>=', $date3)->count();
        $date6 = $this->getPossibleDate('6months');
        $count6Month = $class::whereDate('end_at', '>=', $date6)->count();
        $countAll = $class::count();

        $this->vars['countMonth'] = $countMonth;
        $this->vars['count3Month'] = $count3Month;
        $this->vars['count6Month'] = $count6Month;
        $this->vars['aggregateClass'] = $aggregateClass;
        $this->vars['countAll'] = $countAll;
        return $this->makePartial('$/waka/agg/behaviors/aggregate/_aggregate_popup.htm');

    }

    public function onAggregateValidation()
    {

        $this->controller->onAutoCreateAllAggregation();
        $typeLot = post('typeLot');
        $aggregateClass = post('aggregateClass');
        $cross = post('cross');

        // trace_log("typeLot : " . $typeLot);
        // trace_log("aggregateClass : " . $aggregateClass);
        // trace_log("cross : " . $cross);

        $class = new $aggregateClass;
        $date = $this->getPossibleDate($typeLot);

        if (!$cross) {
            $class::whereDate('end_at', '>=', $date)->update(['is_ready' => false]);
            $modelsToAggregate = $class::where('is_ready', false)->get();
            foreach ($modelsToAggregate as $model) {
                $this->onAggregateOne($model->id, $aggregateClass, $model->data_source->agg_class);
            }
        } else {
            \Waka\Agg\Models\AgYear::whereDate('end_at', '>=', $date)->update(['is_ready' => false]);
            $modelsToAggregate = \Waka\Agg\Models\AgYear::where('is_ready', false)->get();
            foreach ($modelsToAggregate as $model) {
                $this->onAggregateOne($model->id, 'Waka\Agg\Models\AgYear', $model->data_source->agg_class);
            }
            \Waka\Agg\Models\AgMonth::whereDate('end_at', '>=', $date)->update(['is_ready' => false]);
            $modelsToAggregate = \Waka\Agg\Models\AgMonth::where('is_ready', false)->get();
            foreach ($modelsToAggregate as $model) {
                $this->onAggregateOne($model->id, 'Waka\Agg\Models\AgMonth', $model->data_source->agg_class);
            }
            \Waka\Agg\Models\AgWeek::whereDate('end_at', '>=', $date)->update(['is_ready' => false]);
            $modelsToAggregate = \Waka\Agg\Models\AgWeek::where('is_ready', false)->get();
            foreach ($modelsToAggregate as $model) {
                $this->onAggregateOne($model->id, 'Waka\Agg\Models\AgWeek', $model->data_source->agg_class);
            }
        }
        \Flash::info("Le calcul des agrégation est en cours, vous pouvez verifier la progression des calculs dans REGLAGES->TACHES");

    }

    public function onAggregateOne($modelId, $aggregateClass, $aggClass = null)
    {
        // trace_log($aggregateClass);
        // trace_log($modelId);
        // trace_log($aggClass);
        if (!$aggClass) {
            $agg = new $aggregateClass;
            $agg = $aggregateClass::find($modelId);
            $aggClass = $agg->data_source->agg_class;
        }
        $jobId = \Queue::push($aggClass . '@fire', ['class' => $aggregateClass, 'modelId' => $modelId]);
        \Event::fire('job.create.tag', [$jobId, 'Agrégation en attente de calcul']);
    }

}
