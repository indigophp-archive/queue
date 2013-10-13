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
            $this->logger->critical($payload['job'] . ' is not found.', array('payload' => $payload));
            return false;
        }

        // Instantiate job class itself
        $this->instance = new $job[0]($this, $payload['data']);

        // Initialize callables
        $this->execute = $this->failure = array($this->instance);

        // Resolve callable names
        $this->execute[] = @$job[1] ?: 'execute';
        $this->failure[] = @$job[2] ?: 'failure';

        // Check if execute is callable
        if ( ! is_callable($this->execute)) {
            $this->logger->critical($this->execute[1] . 'method in ' . $payload['job'] . ' cannot be called.', array('payload' => $payload));
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
            empty($instance->delete) or $this->delete();
        } catch (\Exception $e) {
            // Execute failure callable
            $failure = is_callable($this->failure) ? call_user_func_array($this->failure, array($this, $e)) : false;

            is_callable($this->failure) or $this->logger->debug('Failure callback in ' . $payload['job'] . ' is not found.', array('payload' => $payload));

            // Do further processing when it returns with false or error
            if ($failure === false) {
                // Should it be automatically retried or just remove it?
                if (isset($instance->retry)) {
                    // Limit or max attempts reached
                    if (is_int($instance->retry) and $this->attempts() >= $instance->retry) {
                        // Bury or delete it if enabled
                        if (isset($instance->bury)) {
                            $this->bury();
                        } elseif (isset($instance->delete)) {
                            $this->delete();
                        }
                    } else {
                        // Release it with a delay
                        $delay = ! empty($instance->delay) ? $instance->delay : 0;
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
