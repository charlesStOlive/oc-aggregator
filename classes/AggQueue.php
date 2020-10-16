<?php namespace Waka\Agg\Classes;

use Waka\Utils\Classes\DataSource;

class AggQueue
{
    public function fire($job, $datas)
    {
        //trace_log('lancement du queue');
        if ($job) {
            \Event::fire('job.start.agg', [$job, "création lot d'agrégation"]);
        }

        $class = $datas['class'];
        $ids = $datas['ids'];
        $logId = $datas['logId'];

        $ds = new DataSource($class, 'class');
        $aggConfig = $ds->getAggConfig();
        $aggConfig->setLogId($logId);
        $aggConfig->launchAll($ids);
        

        if ($job) {
            \Event::fire('job.end.agg', [$job]);
            $job->delete();
        }

    }

}
