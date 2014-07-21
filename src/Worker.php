<?php

/*
 * This file is part of the Indigo Queue package.
 *
 * (c) Indigo Development Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Indigo\Queue;

use Indigo\Queue\Connector\ConnectorInterface;
use Indigo\Queue\Job\JobInterface;
use Indigo\Queue\Connector\DirectConnector;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Exception;
use InvalidArgumentException;

/**
 * Worker class
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
class Worker implements LoggerAwareInterface
{
    /**
     * Queue name
     *
     * @var string
     */
    protected $queue;

    /**
     * Connector
     *
     * @var ConnectorInterface
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
    protected static $config = array(
        'retry'  => 0,
        'delay'  => 0,
        'delete' => false
    );

    public function __construct($queue, ConnectorInterface $connector)
    {
        if ($connector instanceof DirectConnector) {
            throw new InvalidArgumentException('DirectConnector should not be used in a Worker');
        }

        $this->queue     = $queue;
        $this->connector = $connector;
        $this->logger    = new NullLogger;
    }

    /**
     * Listen to queue
     *
     * @param float   $interval Sleep for certain time if no job is available
     * @param integer $timeout  Wait timeout for pop
     *
     * @codeCoverageIgnore
     */
    public function listen($interval = 5, $timeout = 0)
    {
        while (true) {
            // Process the current job if available
            // or sleep for a certain time
            if ($job = $this->getJob($timeout)) {
                $job->execute();
            } elseif ($interval > 0) {
                $this->sleep($interval);
            }
        }
    }

    /**
     * Process one job from the queue
     *
     * @param  integer $timeout Wait timeout for pop
     * @return mixed   Job return value
     */
    public function work($timeout = 0)
    {
        // Only run when valid job object returned
        if ($job = $this->getJob($timeout)) {
            return $job->execute();
        }
    }

    /**
     * Get JobInterface
     *
     * @param  integer      $timeout Wait timeout for pop
     * @return JobInterface Returns null if $job is not a valid JobIterface
     */
    protected function getJob($timeout = 0)
    {
        // Pop job from the queue
        $job = $this->connector->pop($this->queue, $timeout);

        if ($job instanceof LoggerAwareInterface) {
            $job->setLogger($this->logger);
        }

        return $job;
    }

    /**
     * Sleep for a certain time
     *
     * @param float $interval
     *
     * @codeCoverageIgnore
     */
    protected function sleep($interval)
    {
        if ($interval < 1) {
            $interval = $interval / 1000000;
            usleep($interval);
        } else {
            sleep($interval);
        }
    }

    /**
     * Sets a logger
     *
     * @param LoggerInterface $logger
     *
     * @codeCoverageIgnore
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public static function execute(JobInterface $job)
    {
        $payload = $job->getPayload();

        $raw = static::parseJob($payload['job']);

        list($class, $execute, $failure) = $raw;

        try {
            $class = static::resolveClass($class);
        } catch (Exception $e) {
            $this->connector->delete($job);

            return false;
        }
    }

    /**
     * Parse job string
     *
     * @param  string $job Job@execute:failure
     * @return array
     */
    protected static function parseJob($job)
    {
        $job = preg_split('/[:@]/', $job);

        // Make sure we have default values
        return $job + array(null, 'execute', 'failure');
    }

    protected static function resolveClass($class, array $data, JobInterface $job)
    {
        if (!class_exists($class)) {
            $this->logger->log('error', 'Job ' . $class . ' is not found.', $job->getPayload());

        }

        return new $class($job, $data);
    }

    /**
     * Resolve the job
     *
     * @param  array   $payload Job payload
     * @return boolean
     */
    protected static function resolve(array $payload)
    {
        $job = $this->parseJob($payload['job']);

        list($job, $execute, $failure) = $job;

        if (!class_exists($job)) {
            $this->logger->log('error', 'Job ' . $job . ' is not found.');
        }

        $this->job = new $job($this, $payload['data']);
        $this->execute = $this->getCallback($execute);
        $this->failure = $this->getCallback($failure);

        if (property_exists($job, 'config')) {
            $this->config = array_merge($this->config, $job->config);
        }
    }
}
