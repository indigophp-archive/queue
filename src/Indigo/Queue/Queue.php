<?php

/*
 * This file is part of the Indigo Queue package.
 *
 * (c) IndigoPHP Development Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Indigo\Queue;

use Indigo\Queue\Connector\ConnectorInterface;
use Jeremeamia\SuperClosure\SerializableClosure;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Queue class
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
class Queue implements LoggerAwareInterface
{
    /**
     * Connector object
     *
     * @var ConnectorInterface
     */
    protected $connector;

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

    public function __construct($queue, ConnectorInterface $connector)
    {
        $this->queue = $queue;
        $this->connector = $connector;
        $this->logger = new NullLogger;
    }

    /**
    * Create serialized payload
    *
    * @param  string $job
    * @param  mixed  $data
    * @return array
    */
    protected function createPayload($job, array $data = array())
    {
        $payload = array(
            'job'   => $job,
            'data'  => $data,
            'queue' => $this->queue
        );

        // Create special payload if it is a Closure
        if ($job instanceof \Closure) {
            $payload['closure'] = serialize(new SerializableClosure($job));
            $payload['job'] = 'Indigo\\Queue\\Closure';
        }

        return $payload;
    }

    /**
    * Push a new job onto the queue
    *
    * @param  string $job
    * @param  array  $data
    * @param  array  $options
    * @return mixed
    */
    public function push($job, array $data = array(), array $options = array())
    {
        $payload = $this->createPayload($job, $data);

        return $this->connector->push($payload, $options);
    }

    /**
    * Push a new job onto the queue after a delay
    *
    * @param  int    $delay
    * @param  string $job
    * @param  array  $data
    * @param  array  $options
    * @return mixed
    */
    public function delayed($delay, $job, array $data = array(), array $options = array())
    {
        $payload = $this->createPayload($job, $data);

        return $this->connector->delayed($delay, $payload, $options);
    }

    /**
     * Gets the connector
     *
     * @return ConnectorInterface
     */
    public function getConnector()
    {
        return $this->connector;
    }

    /**
     * Sets the connector
     *
     * @param ConnectorInterface $connector
     */
    public function setConnector(ConnectorInterface $connector)
    {
        $this->connector = $connector;

        return $this;
    }

    /**
     * Gets the queue name
     *
     * @return string
     */
    public function getQueue()
    {
        return $this->queue;
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
     * Sets a logger
     *
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function __tostring()
    {
        return $this->queue;
    }
}
