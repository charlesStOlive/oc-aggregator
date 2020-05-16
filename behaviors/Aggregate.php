<?php namespace Waka\Agg\Behaviors;

use Backend\Classes\ControllerBehavior;

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

        trace_log($aggregateClass);

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

    public function onAggregateOne($modelId, $aggregateClass)
    {
        trace_log($aggregateClass);
        trace_log($modelId);
        $agg = new $aggregateClass;
        $agg = $aggregateClass::find($modelId);
        $aggClass = $agg->data_source->agg_class;
        // $aggClass = new $aggClass;
        // $aggClass->fire(null, ['class' => $aggregateClass, 'modelId' => $modelId]);
        \Queue::push($aggClass . '@fire', ['class' => $aggregateClass, 'modelId' => $modelId]);
    }

}
