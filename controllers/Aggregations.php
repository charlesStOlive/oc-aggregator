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
            $lastCreatedYear = $startDateIfEmpty;
        }

        $lastAgM = AgMonth::where('data_source_id', $dataSourceId)->orderBy('end_at', 'desc')->first();
        if ($lastAgM) {
            $lastCreatedMonth = $lastAgM->end_at;
        } else {
            $lastCreatedMonth = $startDateIfEmpty;
        }

        $lastAgW = AgWeek::where('data_source_id', $dataSourceId)->orderBy('end_at', 'desc')->first();
        if ($lastAgW) {
            $lastCreatedWeek = $lastAgW->end_at;
        } else {
            $lastCreatedWeek = $startDateIfEmpty;
        }

        $nextDate = Carbon::now()->addWeeks(6);
        $diffWeeks = $lastCreatedWeek->diffInWeeks($nextDate);
        $diffMonths = $lastCreatedMonth->diffInMonths($nextDate);
        $diffYears = $lastCreatedYear->diffInYears($nextDate);

        trace_log("last----");
        trace_log($lastCreatedWeek);
        trace_log($lastCreatedMonth);
        trace_log($lastCreatedYear);
        trace_log("tomozzow---");
        trace_log($nextDate);
        trace_log("diff---");
        trace_log($diffWeeks);
        trace_log($diffMonths);
        trace_log($diffYears);
        trace_log("di--FIN---ff");

        if ($diffWeeks) {
            for ($w = 1; $w <= $diffWeeks; $w++) {
                $lastCreatedWeek = $lastCreatedWeek->addWeeks(1);
                $week = new AgWeek();
                $week->ag_year = $lastCreatedWeek->year;
                $week->ag_week = $lastCreatedWeek->week;
                $week->data_source_id = $dataSourceId;
                $week->save();
            }
        }
        if ($diffMonths) {
            for ($m = 1; $m <= $diffMonths; $m++) {
                $lastCreatedMonth = $lastCreatedMonth->addMonths(1);
                $month = new AgMonth();
                $month->ag_year = $lastCreatedMonth->year;
                $month->ag_month = $lastCreatedMonth->month;
                $month->data_source_id = $dataSourceId;
                $month->save();
            }
        }
        if ($diffYears) {
            for ($y = 1; $y <= $diffYears; $y++) {
                $lastCreatedYear = $lastCreatedYear->addYear(1);
                $year = new AgYear();
                $year->ag_year = $lastCreatedYear->year;
                $year->data_source_id = $dataSourceId;
                $year->save();
            }
        }

    }

}
