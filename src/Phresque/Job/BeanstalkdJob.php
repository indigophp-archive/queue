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
        $job = $this->getPayload();
        $this->resolve($job);

        try {
            call_user_func_array($this->execute, array($this, $job['data']));
            empty($this->instance->delete) or $this->delete();
        } catch (\Exception $e) {
            if (is_callable($this->failure)) {
                $failure = call_user_func_array($this->failure, array($this, $e));
            }

            if ($failure !== false) {
                if (isset($this->instance->retry)) {
                    if (is_int($this->instance->retry) and $this->attempts() >= $this->instance->retry) {
                        if (isset($this->instance->bury)) {
                            $this->bury();
                        } elseif (isset($this->instance->delete)) {
                            $this->delete();
                        }
                    } else {
                        $delay = ! empty($this->instance->delay) ? $this->instance->delay: 0;
                        $this->release($delay);
                    }
                } else {
                    if (isset($this->instance->bury)) {
                        $this->bury();
                    } elseif (isset($this->instance->delete)) {
                        $this->delete();
                    }
                }
            }
        }
    }

    public function delete()
    {
        $this->connector->delete($this->job);
    }

    public function bury()
    {
        $this->connector->bury($this->job);
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

    public function getPayload()
    {
        return json_decode($this->job->getData(), true);
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
