<?php
/*
 * This file is part of the Indigo Queue package.
 *
 * (c) Indigo Development Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Indigo\Queue\Job;

use Indigo\Queue\Connector\ConnectorInterface;
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
     * Payload
     *
     * @var array
     */
    protected $payload = array();

    /**
     * Queue name
     *
     * @var string
     */
    protected $queue;

    /**
     * Logger instance
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Job object
     *
     * @var object
     */
    protected $job;

    /**
     * Execute callback name
     *
     * @var string
     */
    protected $execute = 'execute';

    /**
     * Failure callback name
     *
     * @var string
     */
    protected $failure = 'failure';

    /**
     * Config values
     *
     * @var array
     */
    protected $config = array(
        'retry'  => 0,
        'delay'  => 0,
        'delete' => false
    );

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        // Get payload here, so we can work with the same data
        $payload = $this->getPayload();

        // Resolve job and delete on error
        if (!$this->resolve($payload)) {
            $this->delete();

            return false;
        }

        try {
            // Run execute callback
            return $this->runExecute($payload);
        } catch (\Exception $e) {
            // Catch any Exceptions
            // Make sure this class does not throw any
            return $this->runFailure($e, $payload);
        }
    }

    /**
     * Resolve the job
     *
     * @param  array   $payload Job payload
     * @return boolean
     */
    public function resolve(array $payload)
    {
        $job = $this->parseJob($payload['job']);

        list($job, $this->execute, $this->failure) = $job;

        $this->job = $this->resolveJob($job, $payload['data']);

        if (!is_object($this->job)) {
            return false;
        }

        $this->config = $this->resolveConfig($this->job);

        return true;
    }

    /**
     * Parse job string
     *
     * @param  string $job Job@execute:failure
     * @return array
     */
    protected function parseJob($job)
    {
        $job = preg_split('/[:@]/', $job);

        // Make sure we have default values
        return $job + array(null, 'execute', 'failure');
    }

    /**
     * Resolve job
     *
     * @param  string      $job
     * @param  array       $data Payload data
     * @return object|null
     */
    protected function resolveJob($job, array $data)
    {
        if (class_exists($job)) {
            return new $job($this, $data);
        }

        $this->log('error', 'Job ' . $job . ' is not found.');
    }

    /**
     * Get config from job
     *
     * @param  object $job
     * @return array  Resolved config
     */
    protected function resolveConfig($job)
    {
        $config = $this->config;

        if (isset($job->config) and is_array($job->config)) {
            $config = array_merge($config, $job->config);
        }

        return $config;
    }

    /**
     * Run execute callback
     *
     * @param  array $payload Job payload
     * @return mixed
     */
    protected function runExecute(array $payload)
    {
        // Check whether we have a valid callback
        if (!$execute = $this->getCallback($this->execute)) {
            $this->log(
                'error',
                "Execute callback '" . $this->execute .
                "' is not found in job " . get_class($this->job) . "."
            );

            return false;
        }

        // Here comes the funny part: execute the job
        $execute = call_user_func($execute, $this, $payload['data']);

        $this->log('debug', 'Job ' . $payload['job'] . ' finished');

        // Try to delete the job if enabled
        $this->tryDelete();

        return $execute;
    }

    /**
     * Run failure callback
     * This should only be run if runExecute throws an exception
     *
     * @param  Exception $e
     * @param  array     $payload Job payload
     * @return mixed
     */
    protected function runFailure(\Exception $e, array $payload)
    {
        if (!$failure = $this->getCallback($this->failure)) {
            $this->log(
                'debug',
                "Failure callback '" . $this->failure .
                "' is not found in job " . get_class($this->job) . "."
            );
        } else {
            $failure = call_user_func($failure, $this, $e, $payload['data']);
        }

        if ($failure === false) {
            $this->failureCallback();
        }
    }

    /**
     * Failure callback is not present or returned false
     *
     * @return boolean
     */
    protected function failureCallback()
    {
        return $this->tryRetry() or $this->tryDelete();
    }

    /**
     * Get callback from string
     *
     * @param  string $callback
     * @return mixed  Callable if callable, default otherwise
     */
    protected function getCallback($callback)
    {
        $callback = array($this->job, $callback);

        return is_callable($callback) ? $callback : false;
    }

    /**
     * Release a job
     *
     * @return boolean Always true
     */
    public function release()
    {
        return $this->getConnector()->release($this, $this->config['delay']);
    }

    /**
     * Try to retry the job
     *
     * @return boolean
     */
    protected function tryRetry()
    {
        return $this->attempts() <= $this->config['retry'] and $this->release();
    }

    /**
     * Delete the job
     *
     * @return boolean Always true
     */
    public function delete()
    {
        return $this->getConnector()->delete($this);
    }

    /**
     * Try to delete the job
     *
     * @return boolean
     */
    protected function tryDelete()
    {
        return $this->config['delete'] === true and $this->delete();
    }

    /**
     * Get connector
     *
     * @return ConnectorInterface
     */
    public function getConnector()
    {
        return $this->connector;
    }

    /**
     * {@inheritdoc}
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * {@inheritdoc}
     */
    public function setPayload(array $payload)
    {
        $this->payload = $payload;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getQueue()
    {
        return $this->queue;
    }

    /**
     * {@inheritdoc}
     */
    public function setQueue($queue)
    {
        $this->queue = $queue;

        return $this;
    }

    /**
     * Get logger
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
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Always include payload as a context in logger
     *
     * @param string $level   Log level
     * @param string $message
     */
    protected function log($level, $message)
    {
        return $this->logger->log($level, $message, $this->getPayload());
    }
}
