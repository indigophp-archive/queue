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

    public function getConnector()
    {
        return $this->connector;
    }

    public function getJob()
    {
        return $this->job;
    }
}
