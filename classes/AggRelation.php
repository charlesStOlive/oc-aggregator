<?php

namespace Waka\Agg\Classes;
use Carbon\Carbon;
use Waka\Agg\Models\Aggeable;
use Waka\Crsm\Models\Uniqueable;

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
    

    public function __construct($relationArray, $relationName, $class) 
    {
        $this->relationName = $relationName;
        $this->config = $relationArray[$relationName];
        $this->queryClass = $this->config['queryClass'];
        $this->morphedName = $this->config['morphedName'];
        $this->class = $class;
    }
    public function getStartAt() {
        $yearStart = $this->config['start_date']['y'];
        $monthStart = $this->config['start_date']['m'];
        return Carbon::createFromDate($yearStart,$monthStart , 1);
    }
    public function getEndAt() {
        return Carbon::now()->addDays($this->config['end_date']);
    }
    public function prepareQuery($model) {
        return $this->query = $model->{$this->relationName}();
    }
    public function setIncludedIds($class, $ids) {
        $listesPeriodes = new \October\Rain\Support\Collection();
        foreach($this->getPeriodeKey() as $periodeSegment) {
            $periodes = $this->getAggPeriode($periodeSegment);
            $listes = new \October\Rain\Support\Collection();
            $listesPeriodes = $listesPeriodes->merge($periodes->listPeriode());
        }
        // $listesPeriodes =  $listesPeriodes->reject(function ($item) {
        //     return $item['type'] ==  'count';
        // });
        
        $idsInculded = $ids;

        // $listesPeriodes = $listesPeriodes->map(function ($item, $key) use($class,$ids) {
        //     //$item['ids'] = $this->getIdsFromPeriod($class, $ids, $item);
        //     return $item ;
        // });

        //trace_log($listesPeriodes->toArray());

        // foreach($listesPeriodes as $listesPeriode) {
        //     if($listesPeriode['periode'] == 'month')
        //     $idsInculded = $this->getIdsFromPeriod($class, $idsInculded, $listesPeriode);
        // }
        // $listes = new \October\Rain\Support\Collection($this->aggPeriode->listPeriode());
        // $listes =  $listes->reject(function ($item) {
        //     return $item['type'] =  'count';
        // });
        //trace_log($listes->toArray());
    }
    public function getIdsFromPeriod($class, $ids, $periode) {
        return $class::whereIn('id',$ids)->whereHas($this->relationName, function($q) use($periode){
            $q->whereBetween('sale_at', [$periode['start_at'], $periode['end_at']]);
        })->get(['id'])->pluck('id')->toArray();

    }
    public function executeAll($ids) {
        foreach($this->getPeriodeKey() as $periodeSegment) {
            //trace_log($periodeSegment);
            $this->executeOne($ids, $periodeSegment);

        }
    }
   

    public function executeOne($ids, $key) {
        //trace_sql();
        // $modelId = $model->id;
        $periodes = $this->getAggPeriode($key);
        $listesPeriodes = $periodes->listPeriode();
        //trace_log($listesPeriodes);
        $injectObject = [];
        $uniqueObject = new \October\Rain\Support\Collection();
        //boucle sur la listePeriode qui renvoi un array avec les principales valeurs. 
       
            /**
             * Abandon de la méthode préscedente trop lente
             * pour trace : 
             */

            // foreach($listesPeriodes as $periode) {
            //     $query = $this->prepareQuery($model)->whereBetween('sale_at', [$periode['start_at'], $periode['end_at']]);
            //     foreach($periode['calculs'] as $calcul) {
            //         if($calcul['type'] == 'sum')  {
            //             $result = $query->sum($calcul['column']);
            //         } 
            //         if($calcul['type'] == 'count')  {
            //             $result = $query->count();
            //         } 
            //         $inject = [
            //             'type' => $key,
            //             'year' =>$periode['year'],
            //             'num' => $periode['num'],
            //             'column' => $periode['column'],
            //             'aggeable_type' =>$type,
            //             'aggeable_id' => $modelId,
            //             'value' =>$result,
            //         ];
            //         array_push($injectObject, $inject);
            //         if($calcul['create_unique'] ?? false) {
            //             $uniqueObject[$periode['create_unique']] = $result;
            //         }
    
            //     }
            /**
             * FIN
             */
            foreach($listesPeriodes as $periode) {
                trace_log("-------------PERIODE-----------------");
                trace_log($periode);

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
