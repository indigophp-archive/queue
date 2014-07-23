<?php

use Indigo\Queue\Job\JobInterface;
use Indigo\Queue\Manager\ManagerInterface;

class SimpleJob implements JobInterface
{
    public function execute(ManagerInterface $manager)
    {
        // Do something

        // This value is returned to the worker
        // and returned by work() method
        return true;
    }

    public function fail(ManagerInterface $manager, \Exception $e)
    {
        // Return has a special meaning
        // In case of false, worker will try to autorelease/bury/delete your job
        // Use it wisely, don't delete the job yourself if you return false
        return true;
    }
}
