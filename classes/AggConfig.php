<?php

namespace Waka\Agg\Classes;
use Waka\Utils\Model\DataSource;
use Carbon\Carbon;

class AggConfig
{
    public $config;
    public $update;
    public $today;
    public $startDay;
    public $aggRelation;
    public $relationArray;
    public $chunk;
    

    public function __construct($config, $class) 
    {
        $this->config = $config;
        $this->update = $config['update'] ?? 700;
        $this->chunk = $config['chunk'] ?? 1000;
        $this->class = $class;

        $this->relationArray = $this->config['relations'];
        $today = Carbon::now();
        $startDay = $today->copy()->subdays($this->update);
    }

    public function launchOne($id) {
        $relationsNames = $this->getRelationKey();
        foreach($relationsNames as $relationName) {
            $aggRelation = $this->getAggRelation($relationName);
            //attention array doit être utilise
            $aggRelation->executeAll([$id]);
        }
    }

    public function launchall(array $ids) {
        $relationsNames = $this->getRelationKey();
        foreach($relationsNames as $relationName) {
            $aggRelation = $this->getAggRelation($relationName);
            $aggRelation->executeAll($ids);
        }
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
