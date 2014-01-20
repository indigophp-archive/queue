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
     * Payload
     *
     * @var array
     */
    protected $payload = array();

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
        'bury'   => false,
        'delete' => false
    );

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $payload = $this->getPayload();

        // Resolve job and delete on error
        if (!$this->resolve($payload)) {
            $this->delete();
            return false;
        }

        try {
            $execute = $this->runExecute($payload['data']);

            return $execute;
        } catch (\Exception $e) {
            $failure = $this->runFailure($e, $payload['data']);

            if ($failure === false) {
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
     * {@inheritdoc}
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * Resolve the job
     *
     * @param  array   $payload  Job payload
     * @return boolean
     */
    public function resolve(array $payload)
    {
        $job = $this->parseJob($payload['job']);

        list($this->job, $this->execute, $this->failure) = $job;

        $this->job = $this->resolveJob($this->job, $payload['data']);

        if (!is_object($this->job)) {
            return false;
        }

        $this->config  = $this->resolveConfig($this->job);

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

        return $job + array(null, 'execute', 'failure');
    }

    /**
     * Resolve job
     *
     * @param  string $job
     * @param  array  $data Payload data
     * @return object|null
     */
    protected function resolveJob($job, $data)
    {
        if (!class_exists($job)) {
            $this->log('error', 'Job ' . $job . ' is not found.');

            return;
        }

        return new $job($this, $data);
    }

    /**
     * Check whether execute is a valid callback
     *
     * @param  string $execute
     * @param  object $job
     * @return boolean
     */
    protected function checkExecute($execute, $job)
    {
        if (!$check = is_callable(array($job, $execute))) {
            $this->log(
                'error'
                "Execute callback '" . $execute . "' is not found in job " . get_class($job) . "."
            );
        }

        return $check;
    }

    /**
     * Resolve failure callback
     *
     * @param  string $failure
     * @param  object $job
     * @return string|null
     */
    protected function resolveFailure($failure, $job)
    {
        if (!is_callable(array($job, $failure))) {
            $this->log(
                'debug'
                "Failure callback '" . $failure . "' is not found in job " . get_class($job) . "."
            );

            return null;
        }

        return $failure;
    }

    /**
     * Get config from job
     *
     * @param  object $job
     * @return array Resolved config
     */
    protected function resolveConfig($job)
    {
        $config = $this->config;

        if (isset($job->config) and is_array($job->config))
        {
            $config = array_merge($config, $job->config);
        }

        return $config;
    }

    /**
     * Run execute callback
     *
     * @param  array  $data
     * @return mixed
     */
    protected function runExecute(array $data)
    {
        $execute = array($this->job, $this->execute);

        if (!is_callable($execute)) {
            # code...
        }

        $execute = call_user_func($execute, $this, $data);

        $this->log('debug', 'Job ' . $payload['job'] . ' finished');

        $this->tryDelete();

        return $execute;
    }

    protected function runFailure(\Exception $e, array $data)
    {
        if ($this->failure) {
            $failure = array($this->job, $this->failure);
            $failure = call_user_func($failure, $this, $data);
        } else {
            $failure = false;
        }

        if ($failure === false) {
            # code...
        }

        $this->tryDelete();

        return $failure;
    }

    protected function getCallback()
    {
        return array($this->getJob());
    }

    /**
     * Get logger instance
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
     * @param  string $level   Log level
     * @param  string $message
     */
    protected function log($level, $message)
    {
        return $this->logger->log($level, $message, $this->getPayload());
    }
}
