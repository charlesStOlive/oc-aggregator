<?php namespace Waka\Agg\Console;

use Illuminate\Console\Command;
//use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Waka\Agg\Models\AgMonth;
use Waka\Agg\Models\AgWeek;
use Waka\Agg\Models\AgYear;
use Waka\Utils\Models\DataSource;

class CreateAggTable extends Command
{
    /**
     * @var string The console command name.
     */
    protected $name = 'waka:agg';

    /**
     * @var string The console command description.
     */
    protected $description = "Creer les lignes des tables d'agregation";

    /**
     * Execute the console command.
     * @return void
     */
    public function handle()
    {
        // $startYear = $this->ask('Année de depart');
        // $endYear = $this->ask('Année de fin');
        $startYear = $this->argument('start_y');
        $endYear = $this->argument('end_y');
        $model = $this->argument('model');

        $modelId = DataSource::where('name', $model)->first()->id;

        trace_log($startYear);
        trace_log($endYear);
        trace_log($model);

        for ($y = $startYear; $y <= $endYear; $y++) {
            $year = new AgYear();
            $year->ag_year = $y;
            $year->data_source_id = $modelId;
            $year->save();
            for ($m = 1; $m <= 12; $m++) {
                $month = new AgMonth();
                $month->ag_year = $y;
                $month->ag_month = $m;
                $month->data_source_id = $modelId;
                $month->save();
            }
            for ($w = 1; $w <= 52; $w++) {
                $week = new AgWeek();
                $week->ag_year = $y;
                $week->ag_week = $w;
                $week->data_source_id = $modelId;
                $week->save();
            }
        }

    }

    /**
     * Get the console command arguments.
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['start_y', InputArgument::REQUIRED, 'Annee Debut'],
            ['end_y', InputArgument::REQUIRED, 'Anne FIN'],
            ['model', InputArgument::REQUIRED, 'la source'],
        ];
    }

    /**
     * Get the console command options.
     * @return array
     */
    protected function getOptions()
    {
        return [];
    }

}
