<?php

namespace Phresque\Job;

use Phresque\LoggerEventAbstract;

abstract class AbstractJob extends LoggerEventAbstract implements JobInterface
{
    /**
     * Job object
     *
     * @var object
     */
    protected $job;

    /**
     * Job handler instance
     *
     * @var object
     */
    protected $instance;

    /**
     * Execute callback
     * @var callback
     */
    protected $execute;

    /**
     * Failure callback
     * @var callback
     */
    protected $failure;

    /**
     * Connector object
     *
     * @var object
     */
    protected $connector;

    public function resolve($payload)
    {
        $job = preg_split('/[:@]/', $payload['job']);

        $this->instance = new $job[0]($this, $payload['data']);

        $this->execute = $this->failure = array($this->instance);

        $this->execute[] = @$job[1] ?: 'execute';
        $this->failure[] = @$job[2] ?: 'failure';
    }

    public function _execute(array $payload)
    {
        $this->resolve($payload);

        try {
            call_user_func_array($this->execute, array($this, $payload['data']));
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

    public function getConnector()
    {
        return $this->connector;
    }

    public function getJob()
    {
        return $this->job;
    }
}
