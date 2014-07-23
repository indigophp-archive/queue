<?php

use Indigo\Queue\Job\JobInterface;
use Indigo\Queue\Manager\ManagerInterface;

class AdvancedJob implements JobInterface
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
    );

    public function execute(ManagerInterface $manager)
    {
        // Do something
        throw new \Exception('Testing exceptions');


        // This won't run
        $job->delete();
    }

    public function fail(ManagerInterface $manager, \Exception $e)
    {
        $job->delete();
        // DON'T DO THIS
        // This function MUST NOT throw any exceptions
        // If it does, the worker will exit
        throw $e;
    }
}
