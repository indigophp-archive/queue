<?php

namespace Phresque\Job;


use Pheanstalk_Job;
use Pheanstalk_Pheanstalk as Pheanstalk;

class BeanstalkdJob extends AbstractJob
{
    public function __construct(Pheanstalk $connector, Pheanstalk_Job $job)
    {
        $this->job = $job;
        $this->connector = $connector;
    }

    public function execute()
    {
        $job = json_decode($this->job->getData(), true);
        $this->resolve($job);

        try {
            call_user_func_array($this->execute, array($job['data'], $this));
            isset($instance->delete) and $this->delete();
        } catch (\Exception $e) {
            if (is_callable($this->failure)) {
                call_user_func_array($this->failure, array($e, $this));
            }
        }
    }

    public function delete()
    {
        $this->connector->delete($this->job);
    }

    public function release($delay = 0, $priority = Pheanstalk::DEFAULT_PRIORITY)
    {
        $this->connector->release($this->job, $priority, $delay);
    }

    public function attempts()
    {
        $stats = $this->connector->statsJob($this->job);

        return (int) $stats->reserves;
    }

    public function __call($method, $params)
    {
        switch (true) {
            case is_callable(array($this->job, $method)):
                return call_user_func_array(array($this->job, $method), $params);
                break;
            case is_callable(array($this->connector, $method)):
                return call_user_func_array(array($this->connector, $method), $params);
                break;
            default:
                # code...
                break;
        }
    }
}
