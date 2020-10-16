<?php

namespace Waka\Agg\Classes;
use Carbon\Carbon;
use Waka\Agg\Models\Aggeable;
use Waka\Crsm\Models\Uniqueable;
use Waka\Crsm\Models\AggeableLog;
//use Waka\Utils\Classes\DataSource;

class AggRelation
{
    public $config;
    public $label;
    public $relationName;
    public $relationModels;
    public $relationClass;
    public $aggPeriode;
    public $start_at;
    public $end_at;
    public $query;
    public $queryClass;
    //public $lastAnalyserDow;
    

    public function __construct($relationArray, $relationName, $class) 
    {
        $this->relationName = $relationName;
        $this->config = $relationArray[$relationName];
        $this->queryClass = $this->config['queryClass'];
        $this->morphedName = $this->config['morphedName'];
        $this->class = $class;
        //$this->ds = new DataSource($class, 'class');
        // $this->$lastAnalyserDow = null;
    }
    // public function createStartLog() {
    //     $lastAgg = AggeableLog::where('data_source_id', $this->ds->id)->max('last_upadated_at')->get(['id', 'last_upadated_at']);
    //     if($lastAgg) {
    //         $this->$lastAnalyserDow = $lastAgg->last_upadated_at;
    //     }
    // }
    public function getStartAt() {
        $yearStart = $this->config['start_date']['y'];
        $monthStart = $this->config['start_date']['m'];
        return Carbon::createFromDate($yearStart,$monthStart , 1);
    }
    public function getEndAt() {
        return Carbon::now()->addDays($this->config['end_date']);
    }
    
    public function executeAll($ids) {
        foreach($this->getPeriodeKey() as $periodeSegment) {
            $this->executeOne($ids, $periodeSegment);
        }
    }
   

    public function executeOne($ids, $key) {
        //trace_sql();
        $periodes = $this->getAggPeriode($key);
        $listesPeriodes = $periodes->listPeriode();
        //trace_log($listesPeriodes);
        $injectObject = [];
        $uniqueObject = new \October\Rain\Support\Collection();
        //boucle sur la listePeriode qui renvoi un array avec les principales valeurs. 
            foreach($listesPeriodes as $periode) {
                // trace_log("-------------PERIODE-----------------");
                // trace_log($periode);

                $results = $this->queryClass::get($ids, $periode);
                foreach ($ids as $id) {
                    foreach ($periode['calculs'] as $calcul) {
                        $result = $results->where('id', $id)->first();
                        $finalResult;
                        if($result) {
                            if($calcul['type'] == 'count') {
                                $finalResult = $result->count;
        
                            }
                            if($calcul['type'] == 'sum') {
                                $finalResult = $result->{$calcul['column']};
                                
                            }
                        } else {
                            $finalResult = 0;
                        }
                    
                        
                        // trace_log($calcul);
                        // trace_log($result);
                        $inject = [
                                'type' => $key,
                                'year' =>$periode['year'],
                                'num' => $periode['num'],
                                'column' => $calcul['column'],
                                'agg' => $calcul['type'],
                                'aggeable_type' =>$this->morphedName,
                                'aggeable_id' => $id,
                                'value' =>$finalResult,
                            ];
                            array_push($injectObject, $inject);
                            // trace_log($inject);
                            // trace_log('createUnique'. $calcul['createUnique']);
                            if($calcul['createUnique'] ?? false) {
                                // trace_log('il y a un calcul');
                                $obj = [
                                    'id' => $id,
                                    'column' =>$calcul['createUnique'],
                                    'result' => $finalResult,
                                ];
                                $uniqueObject->push($obj);
                            }
                    }
            }
            
        }
        //trace_log($injectObject);

        $uniqueObjectInjection = $uniqueObject->groupBy('id')->map(function ($items, $key) {
            $newItem = [];
            foreach($items as $item) {
                $newItem[$item['column']] =  $item['result'];
            }
            return $newItem;
        });
        //trace_log($uniqueObjectInjection->toArray());
        // trace_log('delete');
        // trace_log($this->class);
        // trace_log($ids);
        // trace_log('fin du delete');
        Aggeable::where('aggeable_type', $this->morphedName)->whereIn('aggeable_id',$ids )->delete();
        Aggeable::insert($injectObject);
        foreach($uniqueObjectInjection as $key=>$unique) {
            $this->class::find($key)->uniqueable()->updateOrCreate([],$unique);
        }
        
    }
    public function getPeriodeKey()
    {
        return array_keys($this->config['periodes']);
    }
    public function getAggPeriode($key) 
    {
        $this->start_at = $this->getStartAt();
        $this->end_at = $this->getEndAt();
        $this->aggPeriode =  new AggPeriode($this->config['periodes'][$key], $key, $this->start_at, $this->end_at);
        return $this->aggPeriode;
    }
    
    

}
