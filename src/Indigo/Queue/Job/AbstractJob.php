<?php
/*
 * This file is part of the Indigo Queue package.
 *
 * (c) IndigoPHP Development Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Indigo\Queue\Job;

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
     * Connector object
     *
     * @var ConnectorInterface
     */
    protected $connector;

    /**
     * Resolved job instance
     *
     * @var object
     */
    protected $instance;

    /**
     * Execute callback
     *
     * @var string
     */
    protected $execute;

    /**
     * Failure callback
     *
     * @var string
     */
    protected $failure;

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
     * @param  array  $payload  Job payload
     * @return object           Resolved job class
     */
    public function resolve(array $payload)
    {
        // Resolve execute and failure callables
        $job = preg_split('/[:@]/', $payload['job']);

        // Check if class exists
        if ( ! class_exists($job[0], true)) {
            $this->logger->critical('Job ' . $payload['job'] . ' is not found.', $payload);
            return false;
        }

        // Instantiate job class itself
        $instance = new $job[0]($this, $payload['data']);

        // Resolve callables
        $this->execute = isset($job[1]) ? $job[1] : 'execute';
        $failure = isset($job[2]) ? $job[2] : 'failure';

        if ( ! is_callable(array($instance, $failure))) {
            $this->logger->debug('Failure callback in job ' . $payload['job'] . ' is not found.', $payload);
        } else {
            $this->failure = $failure;
        }

        // Check if execute is callable
        if ( ! is_callable(array($instance, $this->execute))) {
            $this->logger->critical($this->execute . 'method in job ' . $payload['job'] . ' cannot be called.', $payload);
            return false;
        }

        // Get configuration from job
        if (isset($instance->config) and is_array($instance->config))
        {
            $this->config = array_merge($this->config, $instance->config);
        }

        // Return job class
        return $this->instance = $instance;
    }

    /**
     * Execute job
     *
     * @param  array $payload Payload array
     * @return null
     */
    public function executeJob(array $payload)
    {
        // Resolve job class and callables
        $instance = $this->resolve($payload);

        // Do nothing when instance is false or execute is not callable
        if ($instance === false) {
            $this->delete();
            return false;
        }

        // Callables
        $execute = array($instance, $this->execute);
        $failure = array($instance, $this->failure);

        // Try to execute the job
        try {
            // Execute the job and catch the return value
            $execute = call_user_func($execute, $this, $payload['data']);

            // Auto-delete it if it is enabled
            empty($this->config['delete']) or $this->delete();

            // Log Cube-compatible message of success
            $log = array(
                'type' => $payload['queue'],
                'data' => $payload
            );

            $this->logger->debug('Job ' . $payload['job'] . ' finished', $log);

            return $execute;
        } catch (\Exception $e) {
            // Are we sure that we want to do further processing?
            $failure = isset($this->failure) ? call_user_func(array($instance, $this->failure), $this, $e, $payload['data']) : false;

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
     * Sets a logger instance on the object
     *
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}
