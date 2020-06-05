<?php namespace Waka\Agg\Behaviors;

use Backend\Classes\ControllerBehavior;
use Carbon\Carbon;

class Aggregate extends ControllerBehavior
{
    public function __construct($controller)
    {
        parent::__construct($controller);
    }

    public function onAggregate()
    {
        $modelId = post('modelId');
        $aggregateClass = post('aggregateClass');

        //trace_log($aggregateClass);

        $aggregateTyperClass = new $aggregateClass;
        $agg = $aggregateClass::find($modelId);

        $aggClass = $agg->data_source->agg_class;
        $aggClass = new $aggClass;
        $aggClass->fire(null, ['class' => $aggregateClass, 'modelId' => $modelId]);
        return \Redirect::refresh();
    }

    public function onAggregateChecked()
    {
        $checked = post('checked');
        $aggregateClass = post('aggregateClass');
        foreach ($checked as $modelId) {
            $this->onAggregateOne($modelId, $aggregateClass);
        }
    }

    public function onAggregateBehaviorPopupForm()
    {
        $aggregateClass = post('aggregateClass');
        $class = new $aggregateClass;
        $date = Carbon::now()->format('Y-m-d');
        $countMonth = $class::whereDate('end_at', '>=', $date)->count();
        $date3 = Carbon::now()->subMonth(3)->format('Y-m-d');
        $count3Month = $class::whereDate('end_at', '>=', $date3)->count();
        $date6 = Carbon::now()->subMonth(6)->format('Y-m-d');
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
        $typeLot = post('typeLot');
        $aggregateClass = post('aggregateClass');
        $cross = post('cross');

        //trace_log("typeLot : " . $typeLot);
        //trace_log("aggregateClass : " . $aggregateClass);
        //trace_log("cross : " . $cross);

        $class = new $aggregateClass;
        $date = null;
        if ($typeLot == 'month') {
            $date = Carbon::now()->format('Y-m-d');
        }
        if ($typeLot == '2month') {
            $date = Carbon::now()->subMonth(2)->format('Y-m-d');
        }
        if ($typeLot == '6month') {
            $date = Carbon::now()->subMonth(6)->format('Y-m-d');
        }
        if (!$date) {
            $date = '1999-01-01';
        }
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
            $modelsToAggregate = \Waka\Agg\Models\AgMonth::where('is_ready', false)->get();
            foreach ($modelsToAggregate as $model) {
                $this->onAggregateOne($model->id, 'Waka\Agg\Models\AgWeek', $model->data_source->agg_class);
            }
        }

    }

    public function onAggregateOne($modelId, $aggregateClass, $aggClass = null)
    {
        // trace_log($aggregateClass);
        // trace_log($modelId);
        if (!$aggClass) {
            $agg = new $aggregateClass;
            $agg = $aggregateClass::find($modelId);
            $aggClass = $agg->data_source->agg_class;
        }
        \Queue::push($aggClass . '@fire', ['class' => $aggregateClass, 'modelId' => $modelId]);
    }

}
