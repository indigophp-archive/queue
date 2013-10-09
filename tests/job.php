<?php

class JobTest1
{
    public $retry = 2;

    public $delay = 15;

    public $delete = true;

    public $bury = true;

    public function execute()
    {
        throw new \Exception('Test exception');
    }

    public function failure($job, $e)
    {
        # code...
    }
}

class JobTest2
{
    public $retry = 2;

    public $delay = 15;

    public $delete = true;

    public $bury = true;

    public $log = false;

    public function __construct($job, $data)
    {
        $this->job = $job;
        $this->data = $data;
    }

    public function execute()
    {
        if (empty($this->data))
        {
            throw new \Exception('No data');
        }
    }

    public function failure($e, $job)
    {
        $job->delete();
        return false;
    }
}