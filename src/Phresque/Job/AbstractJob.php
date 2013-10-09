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
abstract class AbstractJob implements JobInterface
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
            return false;
        }

        // Instantiate job class itself
        $this->instance = new $job[0]($this, $payload['data']);

        // Initialize callables
        $this->execute = $this->failure = array($this->instance);

        // Resolve callable names
        $this->execute[] = @$job[1] ?: 'execute';
        $this->failure[] = @$job[2] ?: 'failure';

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

        if ($instance === false) {
            return false;
        }

        // Try to execute the job
        try {
            // Execute the job and catch the return value
            $execute = call_user_func_array($this->execute, array($this, $payload['data']));
            is_null($execute) and $execute = true;

            // Throw an error on false return value
            if ($execute === false) {
                throw new \Exception($this->execute[1] . 'method on ' . $payload['job'] . 'class cannot be run.');
            }

            // Auto-delete it if it is enabled
            empty($instance->delete) or $this->delete();
        } catch (\Exception $e) {
            // Execute failure callable
            $failure = call_user_func_array($this->failure, array($this, $e));
            is_null($failure) and $failure = true;

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
