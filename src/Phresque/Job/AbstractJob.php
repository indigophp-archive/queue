<?php

namespace Phresque\Job;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

abstract class AbstractJob implements JobInterface
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
     *
     * @var callback
     */
    protected $execute;

    /**
     * Failure callback
     *
     * @var callback
     */
    protected $failure;

    /**
     * Connector object
     *
     * @var object
     */
    protected $connector;

    /**
     * Logger instance
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Resolve the job
     *
     * @param  array $payload  Job payload
     * @return object          Resolved job class
     */
    public function resolve($payload)
    {
        $job = preg_split('/[:@]/', $payload['job']);

        $this->instance = new $job[0]($this, $payload['data']);

        $this->execute = $this->failure = array($this->instance);

        $this->execute[] = @$job[1] ?: 'execute';
        $this->failure[] = @$job[2] ?: 'failure';

        return $this->instance;
    }

    public function _execute(array $payload)
    {
        $instance = $this->resolve($payload);

        try {
            call_user_func_array($this->execute, array($this, $payload['data']));
            empty($instance->delete) or $this->delete();
        } catch (\Exception $e) {
            if (is_callable($this->failure)) {
                $failure = call_user_func_array($this->failure, array($this, $e));
            }

            if ($failure !== false) {
                if (isset($instance->retry)) {
                    if (is_int($instance->retry) and $this->attempts() >= $instance->retry) {
                        if (isset($instance->bury)) {
                            $this->bury();
                        } elseif (isset($instance->delete)) {
                            $this->delete();
                        }
                    } else {
                        $delay = ! empty($instance->delay) ? $instance->delay: 0;
                        $this->release($delay);
                    }
                } else {
                    if (isset($instance->bury)) {
                        $this->bury();
                    } elseif (isset($instance->delete)) {
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

    /**
     * Sets a logger instance on the object
     *
     * @param LoggerInterface $logger
     * @return null
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}
