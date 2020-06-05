<?php

namespace Waka\Agg\Classes;

class UniqueAgg
{
    private $targetAgg;
    private $classAgg;
    private $modelAgg;

    public function manual($modelAgg)
    {
        $this->modelAgg = $modelAgg;
        $this->targetAgg = $modelAgg->data_source->modelClass;
        $this->classAgg = get_class($modelAgg);
        $this->check();
    }

    public function fire($job, $datas)
    {
        $this->classAgg = $datas['classAgg'];
        $this->modelAgg = $this->classAgg::find($datas['modelAggId']);
        $this->targetAgg = $this->modelAgg->data_source->modelClass;

        $this->check();
        \Event::fire('job.aggUnique.done');
        if ($job) {
            $job->delete();
        }
    }

    public function check()
    {
        //trace_log("check");
        $today = \Carbon\Carbon::now();
        $year = $today->year;
        $month = $today->month;
        $week = $today->weekOfYear;

        // trace_log($this->modelAgg->ag_month . ' : ' . $month);
        // trace_log($this->modelAgg->ag_year . ' : ' . $year);
        // trace_log($this->modelAgg->ag_week . ' : ' . $week);

        $authorisedAgg = new $this->targetAgg;
        $authorisedAgg = $authorisedAgg::first()->agg;
        //trace_log($authorisedAgg);

        if ($this->classAgg == 'Waka\Agg\Models\AgYear' && array_key_exists('year', $authorisedAgg)) {
            if ($this->modelAgg->ag_year == $year) {
                //trace_log("on update le year");
                $this->update('year_');
            } else if ($this->modelAgg->ag_year == $year - 1) {
                //trace_log("on update le year - 1");
                $this->update('year_', '_m');
            }
        }
        if ($this->classAgg == 'Waka\Agg\Models\AgMonth' && array_key_exists('month', $authorisedAgg)) {
            if (($this->modelAgg->ag_month == $month) && ($this->modelAgg->ag_year == $year)) {
                $this->update('month_');
            } else if (($this->modelAgg->ag_month == $month - 1) && ($this->modelAgg->ag_year == $year)) {
                $this->update('month_', '_m');
            }
        }
        if ($this->classAgg == 'Waka\Agg\Models\AgWeek' && array_key_exists('week', $authorisedAgg)) {
            if (($this->modelAgg->ag_week == $week) && ($this->modelAgg->ag_year == $year)) {
                $this->update('week_');
            } else if (($this->modelAgg->ag_week == $week - 1) && ($this->modelAgg->ag_year == $year)) {
                $this->update('week_', '_m');
            }
        }

    }

    public function update($prefix, $suffix = null)
    {

        //Reinitialiser à 0 les valeurs de l'aggregation
        //trace_log("remise à 0 des élements");
        $this->setAggtoNull($prefix, $suffix);

        //Requête sur les aggregables qui vont mettre à jour les uniqueAgg.
        $targetModel = new $this->targetAgg;
        $targetClassName = $targetModel->getMorphClass();

        $aggregations = \Waka\Agg\Models\Aggregable::where('periodeable_type', $this->classAgg)
            ->where('periodeable_id', $this->modelAgg->id)
            ->where('aggregable_type', $targetClassName)->get();

        //$aggregationData = $target->aggregables()->where('periodeable_type', $this->classAgg);

        //trace_log($aggregations->get()->toArray());

        foreach ($aggregations as $aggregation) {
            $targetId = $aggregation->aggregable->id;
            $targetClass = new $this->targetAgg;
            $target = $targetClass::find($targetId);
            //trace_log("ok update");
            $target->update([
                $prefix . 'nb' . $suffix => $aggregation->nb ?? 0,
                $prefix . 'ms' . $suffix => $aggregation->amount ?? 0,
            ]);
            //trace_log($target->uniqueable);
        }

        // foreach ($targets as $target) {

        //     $aggregationData = $target->aggregables()->where('periodeable_type', $this->classAgg)
        //         ->where('periodeable_id', $this->modelAgg->id)->get();
        //     $datas = null;
        //     if ($aggregationData) {
        //         $aggregationData = $aggregationData->first();
        //     }
        //     if ($aggregationData) {
        //         $aggregationData = $aggregationData->toArray();
        //         $datas = json_encode($aggregationData['datas'] ?? null);
        //     }
        //     if ($target->uniqueable == null) {
        //         $uniqueAgg = new \Waka\Agg\Models\UniqueAgg();
        //         $uniqueAgg->{$prefix . 'nb' . $suffix} = $aggregationData['nb'] ?? 0;
        //         $uniqueAgg->{$prefix . 'ms' . $suffix} = $aggregationData['amount'] ?? 0;
        //         $uniqueAgg->{$prefix . 'datas' . $suffix} = $datas;
        //         $target->uniqueable()->save($uniqueAgg);
        //     } else {
        //         $target->uniqueable()->update([
        //             $prefix . 'nb' . $suffix => $aggregationData['nb'] ?? 0,
        //             $prefix . 'ms' . $suffix => $aggregationData['amount'] ?? 0,
        //             $prefix . 'datas' . $suffix => $datas,
        //         ]);
        //     }
        // }

    }

    // public function createNewUnique()
    // {
    //     $targetModel = new $this->targetAgg;
    //     $targetClassName = $targetModel->getMorphClass();

    //     //trace_log('calcul des relations manquantes');
    //     $emptyRelation = $this->targetAgg::doesnthave('uniqueable');
    //     if (!$emptyRelation->count()) {
    //         //Il ne manque pas de relation on termine
    //         //trace_log("Il ne manque pas de relation");
    //         return;
    //     }
    //     $emptyRelation = $emptyRelation->get();

    //     foreach ($emptyRelation->chunk(2000) as $modelGroup) {
    //         $updates = [];
    //         //trace_log('--begin---------------------------------------------------');
    //         foreach ($modelGroup as $newTarget) {
    //             //trace_log($newTarget->id);
    //             array_push($updates, [
    //                 "uniqueable_type" => $targetClassName,
    //                 "uniqueable_id" => $newTarget->id,
    //             ]);
    //         }
    //         //trace_log('--insert--------------------------------------------------');
    //         \Waka\Agg\Models\UniqueAgg::insert($updates);
    //     }
    // }

    public function setAggtoNull($prefix, $suffix)
    {
        $targetModel = new $this->targetAgg;
        //$targetClassName = $targetModel->getMorphClass();
        $columnToCheck = $prefix . 'nb' . $suffix;

        $modelToZero = $targetModel::where($columnToCheck, '<>', 0)
            ->update([
                $prefix . 'nb' . $suffix => 0,
                $prefix . 'ms' . $suffix => 0,
            ]);
    }

}
