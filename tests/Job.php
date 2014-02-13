<?php

use Indigo\Queue\Job\JobInterface;

class Job
{
    public $config = array(
        'retry' => 2,
        'delete' => true,
        'bury' => true,
    );

    public function execute(JobInterface $job, array $data)
    {
        $job->getLogger();
        return true;
    }

    public function runThis(JobInterface $job, array $data)
    {
        return true;
    }

    public function failThis(JobInterface $job, array $data)
    {
        throw new \RuntimeException;
    }

    public function failedThis(JobInterface $job, \Exception $e, $data)
    {
        $job->getConnector()->delete($job);
        return true;
    }
}
