<?php namespace Waka\Agg\Controllers;

use Backend\Classes\Controller;
use Carbon\Carbon;
use Waka\Agg\Models\AgMonth;
use Waka\Agg\Models\AgWeek;
use Waka\Agg\Models\AgYear;
use Waka\Utils\Models\DataSource;

/**
 * Ag Months Back-end Controller
 */
class Aggregations extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController',
        'Waka.Agg.Behaviors.Aggregate',
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';

    public function onAutoCreateAllAggregation($array_model = null)
    {
        if (!$array_model) {
            $array_model = [
                'region',
                'client',
            ];
        };
        foreach ($array_model as $model) {
            $dataSourceId = DataSource::where('name', $model)->first()->id;
            $this->AutoCreateAggregation($dataSourceId);

        }
        return \Redirect::refresh();
    }

    public function AutoCreateAggregation($dataSourceId)
    {
        $startDateIfEmpty = Carbon::createFromDate(2017, 12, 31);

        $lastAgY = AgYear::where('data_source_id', $dataSourceId)->orderBy('end_at', 'desc')->first();
        if ($lastAgY) {
            $lastCreatedYear = $lastAgY->end_at;
        } else {
            $lastCreatedYear = $startDateIfEmpty->copy();
        }

        $lastAgM = AgMonth::where('data_source_id', $dataSourceId)->orderBy('end_at', 'desc')->first();
        if ($lastAgM) {
            $lastCreatedMonth = $lastAgM->end_at;
        } else {
            $lastCreatedMonth = $startDateIfEmpty->copy();
        }

        $lastAgW = AgWeek::where('data_source_id', $dataSourceId)->orderBy('end_at', 'desc')->first();
        if ($lastAgW) {
            $lastCreatedWeek = $lastAgW->end_at;
        } else {
            $lastCreatedWeek = $startDateIfEmpty->copy();
        }

        $nextDate = Carbon::now()->addWeeks(6);
        $diffWeeks = $lastCreatedWeek->diffInWeeks($nextDate);
        $diffMonths = $lastCreatedMonth->diffInMonths($nextDate);
        $diffYears = $nextDate->year - $lastCreatedYear->year;
        // trace_log('Data Source ID -----------------------------------------------------'.$dataSourceId);
        // trace_log("Derniers mois : ".$lastCreatedMonth->format('Y-m-d'));
        // trace_log("Dernieres semaines : ".$lastCreatedWeek->format('Y-m-d'));
        // trace_log("Derniers annes : ".$lastCreatedYear->format('Y-m-d'));
        // trace_log("Travail sur les weeks----");
        // trace_log("Dernieres semaines : ".$lastCreatedWeek->format('Y-m-d'));
        // trace_log("nextDate : ".$nextDate->format('Y-m-d'));
        // trace_log("diff---" . $diffWeeks);
        if ($diffWeeks) {
            for ($w = 1; $w <= $diffWeeks; $w++) {
                $lastCreatedWeek->addWeeks(1);
                $week = new AgWeek();
                $week->ag_year = $lastCreatedWeek->year;
                $week->ag_week = $lastCreatedWeek->week;
                $week->data_source_id = $dataSourceId;
                //$week->is_ready = true;
                $week->save();
            }
        }

        // trace_log("travai sur les mois----");
        // trace_log("Derniers mois : ".$lastCreatedMonth->format('Y-m-d'));
        // trace_log("nextDate : ".$nextDate->format('Y-m-d'));
        // trace_log("diff---" . $diffMonths);
        if ($diffMonths) {
            for ($m = 1; $m <= $diffMonths; $m++) {
                $lastCreatedMonth->addMonths(1);
                $month = new AgMonth();
                $month->ag_year = $lastCreatedMonth->year;
                $month->ag_month = $lastCreatedMonth->month;
                $month->data_source_id = $dataSourceId;
                //$month->is_ready = true;
                $month->save();
            }
        }

        // trace_log("travail sur les années----");
        // trace_log("Derniers annes : ".$lastCreatedYear->format('Y-m-d'));
        // trace_log("nextDate : ".$nextDate->format('Y-m-d'));
        // trace_log("diff---" . $diffYears);
        if ($diffYears) {
            for ($y = 1; $y <= $diffYears; $y++) {
                $lastCreatedYear->addYear(1);
                $year = new AgYear();
                $year->ag_year = $lastCreatedYear->year;
                $year->data_source_id = $dataSourceId;
                //$year->is_ready = true;
                $year->save();
            }
        }

    }

}
