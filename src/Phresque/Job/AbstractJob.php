<?php
/*
 * This file is part of the Phresque package.
 *
 * (c) Márk Sági-Kazár <mark.sagikazar@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phresque\Job;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

/**
 * Abstract Job class
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
abstract class AbstractJob implements JobInterface, LoggerAwareInterface
{
    /**
     * Job object
     *
     * @var object
     */
    protected $job;

    /**
     * Queue object
     *
     * @var QueueInterface
     */
    protected $queue;

    /**
     * Resolved job instance
     *
     * @var object
     */
    protected $instance;

    /**
     * Execute callable
     *
     * @var callback
     */
    protected $execute;

    /**
     * Failure callable
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
     * Config values
     *
     * @var array
     */
    protected $config = array(
        'retry'  => 0,
        'delay'  => 0,
        'bury'   => false,
        'delete' => false
    );

    /**
     * Resolve the job
     *
     * @param  array $payload  Job payload
     * @return object          Resolved job class
     */
    public function resolve($payload)
    {
        // Resolve execute and failure callables
        $job = preg_split('/[:@]/', $payload['job']);

        // Check if class exists
        if ( ! class_exists($job[0], true)) {
            $this->logger->critical('Job {job} is not found.', $payload);
            return false;
        }

        // Instantiate job class itself
        $this->instance = new $job[0]($this, $payload['data']);

        // Resolve callables
        isset($job[1]) or $job[1] = 'execute';
        $this->execute = array($this->instance, $job[1]);
        isset($job[2]) or $job[2] = 'failure';
        $this->failure = array($this->instance, $job[2]);

        // Get configuration from job
        if (isset($this->instance->config) and is_array($this->instance->config))
        {
            $config = array_intersect_key($this->instance->config, $this->config);
            $this->config = array_merge($this->config, $config);
        }

        // Support old method: do not have to use config array
        foreach ($this->config as $key => $value) {
            isset($this->instance->{$key}) and $this->config[$key] = $this->instance->{$key};
        }

        // Check if execute is callable
        if ( ! is_callable($this->execute)) {
            $this->logger->critical($this->execute[1] . 'method in job {job} cannot be called.', $payload);
            return false;
        }

        // Return job class
        return $this->instance;
    }

    /**
     * Run execute callable
     *
     * @param  array  $payload Payload array
     * @return null
     */
    public function runJob(array $payload)
    {
        // Resolve job class and callables
        $instance = $this->resolve($payload);

        // Do not do anything when instance is false or execute is not callable
        if ($instance === false) {
            $this->delete();
            return false;
        }

        // Try to execute the job
        try {
            // Execute the job and catch the return value
            $execute = call_user_func_array($this->execute, array($this, $payload['data']));

            // Auto-delete it if it is enabled
            empty($this->config['delete']) or $this->delete();

            // Log Cube-compatible message of success
            $log = array(
                'handler' => 'CubeHandler',
                'type' => (string) $this->queue,
                'data' => $payload
            );
            $this->logger->debug('Job ' . $payload['job'] . ' finished', $log);

            return $execute;
        } catch (\Exception $e) {
            // Are we sure that we want to do further processing?
            $failure = is_callable($this->failure) ? call_user_func_array($this->failure, array($this, $e)) : false;
            is_callable($this->failure) or $this->logger->debug('Failure callback in job {job} is not found.', $payload);

            // Do further processing when it returns with false or error
            if ($failure === false) {
                // Should it be automatically retried or just bury/remove it?
                if ($this->attempts() <= $this->config['retry']) {
                    // Release it with a delay
                    $this->release($this->config['delay']);
                } elseif ($this->config['bury']) {
                    $this->bury();
                } elseif ($this->config['delete']) {
                    $this->delete();
                }
            }
        }
    }

    /**
     * Get queue connector
     *
     * @return object
     */
    public function getConnector()
    {
        return $this->connector;
    }

    /**
     * Get job object
     *
     * @return object
     */
    public function getJob()
    {
        return $this->job;
    }

    /**
     * Gets a logger instance on the object
     *
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
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
