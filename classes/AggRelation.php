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
            //Aggeable::where('aggeable_type', $this->morphedName)->delete();
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
        $injectObject = [];
        $uniqueObject = new \October\Rain\Support\Collection();

        $olderAggRequest = Aggeable::where('aggeable_type', $this->morphedName)
            ->whereIn('aggeable_id', $ids)
            ->where('type', $key);
        //boucle sur la listePeriode qui renvoi un array avec les principales valeurs.
        foreach ($listesPeriodes as $periode) {
            //trace_log($periode);
            $results = $this->queryClass::get($ids, $periode);
            $calculData = $periode['calculs'];
            //     foreach ($periode['calculs'] as $calcul) {
            //         //lancement boucle periode
            foreach ($ids as $id) {
                //lancement boucle ID
                $result = $results->where('id', $id)->first();
                $finalResult = [];
                if ($result) {
                    $finalResult['count'] = $result->count;
                    $finalResult['sum'] = $result->{$calculData['column']};
                }
                $inject = [
                    'aggeable_type' => $this->morphedName,
                    'aggeable_id' => $id,
                    'type' => $key,
                    'year' => $periode['year'],
                    'num' => $periode['num'],
                    'column' => $calculData['column'],
                    'sum' => $finalResult['sum'] ?? 0,
                    'count' => $finalResult['count'] ?? 0,
                    'ended_at' => $periode['end_at'],
                ];
                array_push($injectObject, $inject);

                // // trace_log($calcul);
                // // trace_log($result);

                // // trace_log($inject);
                // // trace_log('createUnique'. $calcul['createUnique']);
                $createUnique = $calculData['createUnique'] ?? false;
                if ($createUnique) {
                    foreach ($createUnique as $uniqueType => $uniqueColumn) {
                        // trace_log('il y a un calcul');
                        $obj = [
                            'id' => $id,
                            'column' => $uniqueColumn,
                            'result' => $finalResult[$uniqueType] ?? 0,
                        ];
                        $uniqueObject->push($obj);
                    }
                }
            }
            //trace_log($uniqueObject->toArray());
            //suppression de toutes les lignes avant INSERT de masse
            //trace_sql();
            $olderAggRequest->where('year', $periode['year'])->where('num', $periode['num'])->where('column', $calculData['column']);
            //trace_log("Nombre de ligne à supprimer");
            //trace_log($olderAggRequest->count());
            $olderAggRequest->delete();

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
