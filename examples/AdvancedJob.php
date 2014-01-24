<?php

use Indigo\Queue\Job\JobInterface;

class AdvancedJob
{
    protected $data;

    public $config = array(
        /**
         * Max attempts of a job before burying/deleting
         */
        'retry' => 2,

        /**
         * Release job with a delay after it failed
         */
        'delay' => 5,

        /**
         * Autodelete job if success or failed and retry is disabled or reached max attempts.
         */
        'delete' => false,

        /**
         * Autobury job if failed and retry is disabled or reached max attempts.
         * Bury is higher than delete, so after failing worker will first bury it, if can.
         */
        'bury' => true,
    );

    // This is optional, but if your job has a constructor,
    // it must follow this implementation
    public function __construct(JobInterface $job, array $data)
    {
        $this->data = $data;
    }

    // See Queue example for name usage
    public function exec(JobInterface $job, array $data)
    {
        // Do something
        throw new \Exception('Testing exceptions');


        // This won't run
        $job->delete();
    }

    // See Queue example for name usage
    public function fail(JobInterface $job, \Exception $e, array $data)
    {
        $job->delete();
        // DON'T DO THIS
        // This function MUST NOT throw any exceptions
        // If it does, the worker will exit
        throw $e;
    }
}
