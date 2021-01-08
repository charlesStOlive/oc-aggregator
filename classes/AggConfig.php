<?php

namespace Waka\Agg\Classes;

use Carbon\Carbon;
use Waka\Agg\Models\AggeableLog;

class AggConfig
{
    public $config;
    public $update;
    public $aggRelation;
    public $relationArray;
    public $dataSourceId;
    public $chunk;
    public $logId;

    public function __construct($config, $class)
    {
        $this->config = $config;
        $this->update = $config['update'] ?? 700;
        $this->chunk = $config['chunk'] ?? 1000;
        $this->class = $class;

        $this->relationArray = $this->config['relations'];
    }

    public function setLogId($logId)
    {
        $this->logId = $logId;
    }

    public function launchOne($id)
    {
        $relationsNames = $this->getRelationKey();
        foreach ($relationsNames as $relationName) {
            $aggRelation = $this->getAggRelation($relationName);
            //attention array doit être utilise
            $aggRelation->executeAll([$id]);
        }
    }

    // public function getOldestRow()
    // {
    //     $lastAgg = AggeableLog::where('data_source', $this->ds->code)->whereNotNull('ended_at')->orderBy('taken_at', 'desc')->first();
    //     if ($lastAgg) {
    //         $relatedClass = $this->class::{$this->relationName}()->getRelated();
    //         $oldestRow = $relatedClass::where('updated_at', '>', $lastAgg->taken_at)->orderBy($this->dateColumn, 'desc')->first();
    //         return $oldestRow ? $oldestRow[$this->dateColumn] : 'STOP';
    //     } else {
    //         Aggeable::where('aggeable_type', $this->config['morphedName']);
    //         return null;
    //     }

    // }

    public function launchall(array $ids)
    {
        $relationsNames = $this->getRelationKey();

        foreach ($relationsNames as $relationName) {
            $aggRelation = $this->getAggRelation($relationName);
            $aggRelation->executeAll($ids, true); //avec true on prend en compte l'historique
        }
        $log = AggeableLog::find($this->logId);
        //trace_log($this->logId);
        $log->parts_ended = $log->parts_ended + 1;
        if ($log->parts == $log->parts_ended) {
            $log->ended_at = Carbon::now();
        }
        $log->save();
    }

    public function getConfig()
    {
        return $this->config;
    }
    public function getRelationKey()
    {
        return array_keys($this->relationArray);
    }
    public function getAggRelation($key)
    {
        return $this->aggRelation = new AggRelation($this->relationArray, $key, $this->class);
    }

}
