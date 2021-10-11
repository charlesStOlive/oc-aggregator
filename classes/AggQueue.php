<?php namespace Waka\Agg\Classes;

use Waka\Utils\Classes\DataSource;

class AggQueue
{
    public function fire($job, $datas)
    {
        if ($job) {
            \Event::fire('job.start.agg', [$job, "création lot d'agrégation"]);
        }

        $class = $datas['class'];
        $ids = $datas['ids'];
        $logId = $datas['logId'];

        $ds =  \DataSources::findByClass($class);
        $aggConfig = $ds->getAggConfig();
        $aggConfig->setLogId($logId);
        $aggConfig->launchAll($ids);

        if ($job) {
            \Event::fire('job.end.agg', [$job]);
            $job->delete();
        }
    }
}
