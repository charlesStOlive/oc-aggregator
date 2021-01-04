<?php

namespace Waka\Agg\Classes;

use Carbon\Carbon;

class AggPeriode
{
    public $config;
    public $columnsToSum;
    public $count;
    public $keep;
    public $start_at;
    public $end_at;

    public function __construct(array $config, String $periode, Carbon $start_at, Carbon $end_at)
    {
        $this->config = $config;
        $this->calculs = $config['calculs'];
        $this->periode = $periode;
        //
        $this->start_at = $start_at;
        $this->end_at = $end_at;
        // trace_log('---periode---');
        // trace_log($config);
        // trace_log($this->start_at);
        // trace_log($this->end_at);

    }

    public function listPeriode(Carbon $start_at = null, Carbon $end_at = null)
    {
        if (!$start_at) {
            $start_at = $this->start_at;
        }
        if (!$end_at) {
            $end_at = $this->end_at;
        }
        $result = [];

        if ($this->periode == 'year') {
            $sub_start_at = $start_at->copy();
            $diffYears = $end_at->year - $sub_start_at->year + 1;
            for ($i = 0; $i < $diffYears; $i++) {
                $year = $sub_start_at->year;
                $dateStart = $sub_start_at->copy()->startOfYear();
                $dateEnd = $sub_start_at->copy()->endOfYear();
                //$uniqueArray = $this->isUniqueableValue($configUnique,$this->periode, $dateStart, $dateEnd );
                $obj = [
                    'year' => $year,
                    'num' => null,
                    'periode' => $this->periode,
                    'start_at' => $dateStart->format('Y-m-d'),
                    'end_at' => $dateEnd->format('Y-m-d'),
                    'calculs' => $this->getCalculs($dateStart, $dateEnd),
                ];
                array_push($result, $obj);
                $sub_start_at->addYear();
            }
        }

        if ($this->periode == 'quarter') {
            $sub_start_at = $start_at->copy();
            $diffQuarters = $sub_start_at->diffInQuarters($end_at) + 1;
            for ($i = 0; $i < $diffQuarters; $i++) {
                //trace_log('---------------------------');
                $quarterNum = $sub_start_at->quarter;
                $year = $sub_start_at->year;
                $dateStart = $sub_start_at->copy()->startOfQuarter();
                $dateEnd = $sub_start_at->copy()->endOfQuarter();
                //$uniqueArray = $this->isUniqueableValue($configUnique,$this->periode, $dateStart, $dateEnd );

                $obj = [
                    'year' => $year,
                    'num' => $quarterNum,
                    'periode' => $this->periode,
                    'start_at' => $dateStart->format('Y-m-d'),
                    'end_at' => $dateEnd->format('Y-m-d'),
                    'calculs' => $this->getCalculs($dateStart, $dateEnd),
                ];
                array_push($result, $obj);
                $sub_start_at->addQuarter();
            }

        }

        if ($this->periode == 'month') {
            $sub_start_at = $start_at->copy();
            $diffMonths = $sub_start_at->diffInMonths($end_at) + 1;
            for ($i = 0; $i < $diffMonths; $i++) {
                //trace_log('---------------------------');
                $monthNum = $sub_start_at->month;
                $year = $sub_start_at->year;
                $dateStart = $sub_start_at->copy()->startOfMonth();
                $dateEnd = $sub_start_at->copy()->endOfMonth();
                //$uniqueArray = $this->isUniqueableValue($configUnique,$this->periode, $dateStart, $dateEnd );

                $obj = [
                    'year' => $year,
                    'num' => $monthNum,
                    'periode' => $this->periode,
                    'start_at' => $dateStart->format('Y-m-d'),
                    'end_at' => $dateEnd->format('Y-m-d'),
                    'calculs' => $this->getCalculs($dateStart, $dateEnd),
                ];
                array_push($result, $obj);
                $sub_start_at->addMonth();
            }

        }

        if ($this->periode == 'week') {
            $sub_start_at = $start_at->copy();
            $diffWeeks = $sub_start_at->diffInWeeks($end_at) + 1;
            for ($i = 1; $i < $diffWeeks; $i++) {
                //trace_log('----------week-----------------');
                $dateStart = $sub_start_at->copy()->startOfWeek();
                $dateEnd = $sub_start_at->copy()->endOfWeek();
                $year = $dateEnd->year;
                $weekNum = $dateEnd->weekOfYear;
                if ($weekNum == 53) {
                    $year = $dateStart->year;
                    $i--;
                }
                //$uniqueArray = $this->isUniqueableValue($configUnique,$this->periode, $dateStart, $dateEnd );

                $obj = [
                    'year' => $year,
                    'num' => $weekNum,
                    'periode' => $this->periode,
                    'start_at' => $dateStart->format('Y-m-d'),
                    'end_at' => $dateEnd->format('Y-m-d'),
                    'calculs' => $this->getCalculs($dateStart, $dateEnd),
                ];
                array_push($result, $obj);
                $sub_start_at->addWeek();
            }

        }

        return $result;
    }

    public function getCalculs($start_at, $end_at)
    {
        $calculs = [];
        foreach ($this->calculs as $calculKey => $calculConfig) {
            $createUnique = $calculConfig['create_unique'] ?? false;
            $calcul = [
                'type' => $calculConfig['type'],
                'column' => $calculKey,
                'createUnique' => $this->isUniqueableValue($createUnique, $start_at, $end_at),
            ];
            array_push($calculs, $calcul);
        }
        return $calculs;
    }

    public function isUniqueableValue($configUnique, $start, $end)
    {
        if (!$configUnique) {
            return false;
        }
        $result = [];
        foreach ($this->calculs as $calculKey => $calculConfig) {

            foreach ($configUnique as $key => $value) {
                $date = Carbon::now();
                if ($value != 'now' && $value) {

                    if ($this->periode == 'year') {
                        $date->subYear($value);
                    }
                    if ($this->periode == 'quarter') {
                        $date->subQuarters($value);
                    }
                    if ($this->periode == 'month') {
                        $date->subMonths($value);
                    }
                    if ($this->periode == 'week') {
                        $date->subWeeks($value);
                    }
                }
                // trace_log($date->format('y-m-d'));
                // trace_log($start->format('y-m-d'));
                // trace_log($end->format('y-m-d'));

                //trace_log($date->format('y-m-d').' , '.$start->format('y-m-d').' '.$end->format('y-m-d'));
                if ($date->isBetween($start, $end)) {
                    return $key;
                }
            }
            return false;
        }
    }
}
