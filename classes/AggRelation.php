<?php

namespace Waka\Agg\Classes;

use Carbon\Carbon;
use Waka\Agg\Models\Aggeable;
use Waka\Agg\Models\AggeableLog;
use Waka\Utils\Classes\DataSource;

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
        $this->dateColumn = $this->config['dateColumn'];
        $this->ds = new DataSource($class, 'class');
        //$this->dateOldestRow = $this->getOldestRow();
    }

    public function getOldestRow()
    {
        $lastAgg = AggeableLog::where('data_source', $this->ds->code)->whereNotNull('ended_at')->orderBy('taken_at', 'desc')->first();
        if ($lastAgg) {
            $relatedClass = $this->class::{$this->relationName}()->getRelated();
            $oldestRow = $relatedClass::where('updated_at', '>', $lastAgg->taken_at)->orderBy($this->dateColumn, 'desc')->first();
            return $oldestRow ? $oldestRow[$this->dateColumn] : 'STOP';
        } else {
            return null;
        }
    }

    public function getStartAt()
    {
        $yearStart = $this->config['start_date']['y'];
        $monthStart = $this->config['start_date']['m'];
        return Carbon::createFromDate($yearStart, $monthStart, 1);

    }
    public function getEndAt()
    {
        return Carbon::now()->addDays($this->config['end_date']);
    }

    public function executeAll($ids, $startFromOldAgg = false)
    {
        if ($startFromOldAgg) {
            $this->start_at = $this->getOldestRow();
        }
        if ($this->start_at == 'STOP') {
            //trace_log("Il n' y a rien à faire");
            return;
        }
        foreach ($this->getPeriodeKey() as $periodeSegment) {
            $this->executeOne($ids, $periodeSegment);
        }
    }

    public function executeOne($ids, $key)
    {
        $periodes = $this->getAggPeriode($key);
        $listesPeriodes = $periodes->listPeriode();
        //trace_log($listesPeriodes);
        $injectObject = [];
        $uniqueObject = new \October\Rain\Support\Collection();

        $olderAggRequest = Aggeable::where('aggeable_type', $this->morphedName)
            ->whereIn('aggeable_id', $ids)
            ->where('type', $key);

        // $advancedClean = false;

        // if ($olderAggRequest->count()) {
        //     $advancedClean = true;
        // }
        //boucle sur la listePeriode qui renvoi un array avec les principales valeurs.
        foreach ($listesPeriodes as $periode) {
            $results = $this->queryClass::get($ids, $periode);
            foreach ($periode['calculs'] as $calcul) {
                //lancement boucle periode
                foreach ($ids as $id) {
                    //lancement boucle ID
                    $result = $results->where('id', $id)->first();
                    $finalResult = 0;
                    if ($result) {
                        if ($calcul['type'] == 'count') {
                            $finalResult = $result->count;

                        }
                        if ($calcul['type'] == 'sum') {
                            $finalResult = $result->{$calcul['column']};

                        }
                    }
                    $inject = [
                        'type' => $key,
                        'year' => $periode['year'],
                        'num' => $periode['num'],
                        'column' => $calcul['column'],
                        'agg' => $calcul['type'],
                        'aggeable_type' => $this->morphedName,
                        'aggeable_id' => $id,
                        'value' => $finalResult,
                        'ended_at' => $periode['end_at'],
                    ];
                    array_push($injectObject, $inject);

                    // trace_log($calcul);
                    // trace_log($result);

                    // trace_log($inject);
                    // trace_log('createUnique'. $calcul['createUnique']);
                    if ($calcul['createUnique'] ?? false) {
                        // trace_log('il y a un calcul');
                        $obj = [
                            'id' => $id,
                            'column' => $calcul['createUnique'],
                            'result' => $finalResult,
                        ];
                        $uniqueObject->push($obj);
                    }
                }
                //suppression de toutes les lignes avant INSERT de masse
                //trace_sql();
                $olderAggRequest->where('year', $periode['year'])->where('num', $periode['num'])->where('agg', $calcul['type']);
                trace_log("Nombre de ligne à supprimer");
                trace_log($olderAggRequest->count());
                $olderAggRequest->delete();
            }

        }
        $injectChuncked = array_chunk($injectObject, 1000);
        foreach ($injectChuncked as $inject) {
            Aggeable::insert($inject);
        }
        //trace_log($injectObject);

        //trace_log($uniqueObjectInjection->toArray());
        //trace_log($injectObject);
        //trace_log('--------------------------------------------delete');
        // trace_log($this->class);
        // trace_log($ids);
        // trace_log('fin du delete');

        //Ancien mode on delete en gorupe
        //

        // $injectChuncked = array_chunk($injectObject, 1000);
        // foreach ($injectChuncked as $inject) {
        //     Aggeable::insert($inject);
        // }

        //Création des fichiers unique.

        $uniqueObjectInjection = $uniqueObject->groupBy('id')->map(function ($items, $key) {
            $newItem = [];
            foreach ($items as $item) {
                $newItem[$item['column']] = $item['result'];
            }
            return $newItem;
        });

        foreach ($uniqueObjectInjection as $key => $unique) {
            $this->class::find($key)->unique()->updateOrCreate([], $unique);
        }

    }
    public function getPeriodeKey()
    {
        return array_keys($this->config['periodes']);
    }
    public function getAggPeriode($key)
    {
        if (!$this->start_at) {
            $this->start_at = $this->getStartAt();
        }
        $this->end_at = $this->getEndAt();
        $this->aggPeriode = new AggPeriode($this->config['periodes'][$key], $key, $this->start_at, $this->end_at);
        return $this->aggPeriode;
    }

}
