<?php
/**
 * Copyright (c) 2018 Viamage Limited
 * All Rights Reserved
 */

namespace Waka\Agg\Jobs;

use Waka\Wakajob\Classes\JobManager;
use Waka\Wakajob\Classes\RequestSender;
use Waka\Wakajob\Contracts\WakajobQueueJob;
use Winter\Storm\Database\Model;
use Viamage\CallbackManager\Models\Rate;
use Waka\Segator\Classes\TagCreator;
use Waka\Utils\Classes\DataSource;
use Waka\Agg\Models\AggeableLog;

/**
 * Class SendRequestJob
 *
 * Sends POST requests with given data to multiple target urls. Example of Wakajob Job.
 *
 * @package Waka\Wakajob\Jobs
 */
class AggJob implements WakajobQueueJob
{
    /**
     * @var int
     */
    public $jobId;

    /**
     * @var JobManager
     */
    public $jobManager;

    /**
     * @var array
     */
    private $data;

    /**
     * @var bool
     */
    private $updateExisting;

    /**
     * @var int
     */
    private $chunk;

    /**
     * @var string
     */
    private $table;

    /**
     * @param int $id
     */
    public function assignJobId(int $id)
    {
        $this->jobId = $id;
    }

    /**
     * SendRequestJob constructor.
     *
     * We provide array with stuff to send with post and array of urls to which we want to send
     *
     * @param array  $data
     * @param string $model
     * @param bool   $updateExisting
     * @param int    $chunk
     */
    public function __construct(string $model)
    {
        $this->model = $model;
        $this->updateExisting = true;
        $this->chunk = 1;
    }

    /**
     * Job handler. This will be done in background.
     *
     * @param JobManager $jobManager
     */
    public function handle(JobManager $jobManager)
    {
        /**
         * travail preparatoire sur les donnes
         */
        //trace_log("le job commence");
        $modelClass = $this->model;

        $ds = new DataSource($modelClass, 'class');
        $aggConfig = $ds->getAggConfig();

        $models = $modelClass::get(['id']);
        $modelsChunk = $models->chunk($aggConfig->chunk);

        $today = \Carbon\Carbon::now();

        $aggLog = AggeableLog::create([
            'taken_at' => $today,
            'data_source' => $ds->code,
            'parts' => $modelsChunk->count(),
        ]);
        /**
         * We initialize database job. It has been assigned ID on dispatching,
         * so we pass it together with number of all elements to proceed (max_progress)
         */
        $loop = 1;
        $jobManager->startJob($this->jobId, \count($modelsChunk));
        // Fin inistialisation
        try {
            foreach ($modelsChunk as $models) {
                //trace_log("debut de loop");
                if ($jobManager->checkIfCanceled($this->jobId)) {
                        $jobManager->failJob($this->jobId);
                        break;
                }
                $jobManager->updateJobState($this->jobId, $loop);
                /**
                     * DEBUT TRAITEMENT **************
                */

                $ids = $models->pluck('id')->toArray();

                $aggConfig = $ds->getAggConfig();
                $aggConfig->setLogId($aggLog->id);
                //trace_log("LaunchAll");
                //trace_log($ids);
                $aggConfig->launchAll($ids);
                //trace_log("fin launchAll");
                $loop += $this->chunk;
                $jobManager->updateJobState($this->jobId, $loop);
            }
            $jobManager->completeJob($this->jobId, ['Message' => \Lang::get('waka.agg::lang.job.job_title')]);
        } catch (\Exception $ex) {
            //trace_log("Exception");
            /**/trace_log($ex->getMessage());
            $jobManager->failJob($this->jobId, ['error' => $ex->getMessage()]);
        }
    }
}
