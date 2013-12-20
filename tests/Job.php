<?php

use Indigo\Queue\Job\JobInterface;

class Job
{
    public $config = array(
        'delete' => true
    );

    public function execute(JobInterface $job, array $data)
    {
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
        $job->delete();
        throw $e;
    }
}
