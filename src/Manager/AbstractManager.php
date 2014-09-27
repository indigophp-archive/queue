<?php

/*
 * This file is part of the Indigo Queue package.
 *
 * (c) Indigo Development Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Indigo\Queue\Manager;

use Indigo\Queue\Manager;
use Indigo\Queue\Adapter;
use Indigo\Queue\Job\JobInterface;
use Indigo\Queue\Exception\JobNotFoundException;
use Indigo\Queue\Exception\InvalidJobException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\NullLogger;

/**
 * Abstract Job class
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 *
 * @codeCoverageIgnore
 */
abstract class AbstractManager implements Manager, LoggerAwareInterface
{
    use \Psr\Log\LoggerAwareTrait;

    /**
     * Adapter object
     *
     * @var Adapter
     */
    protected $adapter;

    /**
     * Payload
     *
     * @var []
     */
    protected $payload = [];

    /**
     * Queue name
     *
     * @var string
     */
    protected $queue;

    /**
     * Config values
     *
     * @var []
     */
    protected $config = [
        'retry'  => 0,
        'delay'  => 0,
        'delete' => false,
    ];

    /**
     * Creates a new Adapter
     *
     * @param string    $queue
     * @param Adapter $adapter
     */
    public function __construct($queue, Adapter $adapter)
    {
        $this->queue = $queue;
        $this->adapter = $adapter;

        $this->setLogger(new NullLogger);
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
    public function getQueue()
    {
        return $this->queue;
    }

    /**
     * {@inheritdoc}
     */
    public function getAdapter()
    {
        return $this->adapter;
    }


    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $payload = $this->getPayload();

        // Resolve job
        $job = $this->resolve($payload['job']);

        try {
            // Here comes the funny part: execute the job
            $execute = $job->execute($this);

            $this->log('debug', 'Job ' . $payload['job'] . ' finished');
            $this->autoDelete();

            return $execute;
        } catch (\Exception $e) {
            $failure = $job->fail($this, $e);

            $this->failureCallback($failure);
        }
    }

    /**
     * Resolves the job class
     *
     * @param string $class
     *
     * @return mixed
     *
     * @throws JobNotFoundException If job cannot be found
     * @throws InvalidJobException  If $class is not subclass of JobInterface
     *
     * @codeCoverageIgnore
     */
    protected function resolve($class)
    {
        if (class_exists($class) === false) {
            $message = 'Job ' . $class . ' is not found.';

            $this->log('error', $message);

            throw new JobNotFoundException($message);
        }

        $job = $this->resolveClass($class);

        if ($job instanceof JobInterface === false) {
            throw new InvalidJobException($class . ' is not an instance of Indigo\\Queue\\Job\\JobInterface');
        }

        if (isset($job->config)) {
            $this->config = array_merge($this->config, $job->config);
        }

        return $job;
    }

    /**
     * Returns a new job class
     *
     * @param string $class
     *
     * @return mixed
     *
     * @codeCoverageIgnore
     */
    protected function resolveClass($class)
    {
        if (is_subclass_of($class, 'Indigo\\Queue\\Manager\\FactoryInterface')) {
            return $class::factory($this);
        }

        return new $class;
    }

    /**
     * Failure callback is not present or returned false
     *
     * @param boolean $failure Failure function return value
     *
     * @return boolean
     *
     * @codeCoverageIgnore
     */
    protected function failureCallback($failure)
    {
        return $failure === false and $this->autoRetry() or $this->autoDelete();
    }

    /**
     * Tries to retry the job
     *
     * @return boolean
     *
     * @codeCoverageIgnore
     */
    protected function autoRetry()
    {
        if ($this->attempts() <= $this->config['retry']) {
            return $this->adapter->release($this, $this->config['delay']);
        }
    }

    /**
     * Tries to delete the job
     *
     * @return boolean
     *
     * @codeCoverageIgnore
     */
    protected function autoDelete()
    {
        return $this->config['delete'] === true and $this->adapter->delete($this);
    }

    /**
     * Always include payload as a context in logger
     *
     * @param string $level
     * @param string $message
     *
     * @codeCoverageIgnore
     */
    protected function log($level, $message)
    {
        return $this->logger->log($level, $message, $this->getPayload());
    }
}
