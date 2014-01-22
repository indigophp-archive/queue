<?php

use Indigo\Queue\Job\JobInterface;

class SimpleJob
{
    protected $data;

    // This is optional, but if your job has a constructor,
    // it must follow this implementation
    public function __construct(JobInterface $job, array $data)
    {
        $this->data = $data;
    }

    public function execute(JobInterface $job, array $data)
    {
        // Do something

        // This value is returned to the worker
        // and returned by work() method
        return true;
    }

    public function failure(JobInterface $job, \Exception $e, array $data)
    {
        // Return has a special meaning
        // In case of false, worker will try to autorelease/bury/delete your job
        // Use it wisely, don't delete the job yourself if you return false
        return true;
    }
}
