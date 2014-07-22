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
use Jeremeamia\SuperClosure\SerializableClosure;
use Psr\Log\NullLogger;

/**
 * Queue class
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
class Queue
{
    use \Psr\Log\LoggerAwareTrait;

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
     * Creates a new Queue
     *
     * @param string             $queue
     * @param ConnectorInterface $connector
     */
    public function __construct($queue, ConnectorInterface $connector)
    {
        $this->queue = $queue;
        $this->connector = $connector;

        $this->setLogger(new NullLogger);
    }

    /**
     * Returns the queue name
     *
     * @return string
     */
    public function getQueue()
    {
        return $this->queue;
    }

    /**
     * Returns the connector
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
     *
     * @return this
     */
    public function setConnector(ConnectorInterface $connector)
    {
        $this->connector = $connector;

        return $this;
    }

    /**
    * Creates a serialized payload
    *
    * @param string $job
    * @param []     $data
    *
    * @return []
    */
    public function createPayload($job, array $data = [])
    {
        $payload = [
            'job'   => $job,
            'data'  => $data,
        ];

        // Create special payload if it is a Closure
        if ($job instanceof \Closure) {
            $payload['closure'] = serialize(new SerializableClosure($job));
            $payload['job'] = 'Indigo\\Queue\\Closure';
        }

        return $payload;
    }

    /**
    * Pushes a new job onto the queue
    *
    * @param string $job
    * @param []     $data
    * @param []     $options
    *
    * @return mixed
    *
    * @codeCoverageIgnore
    */
    public function push($job, array $data = [], array $options = [])
    {
        $payload = $this->createPayload($job, $data);

        return $this->connector->push($this->queue, $payload, $options);
    }

    /**
    * Pushes a new job onto the queue after a delay
    *
    * @param integer $delay
    * @param string  $job
    * @param []      $data
    * @param []      $options
    *
    * @return mixed
    *
    * @codeCoverageIgnore
    */
    public function delayed($delay, $job, array $data = [], array $options = [])
    {
        $payload = $this->createPayload($job, $data);

        return $this->connector->delayed($this->queue, $delay, $payload, $options);
    }

    /**
     * Alias to getQueue()
     */
    public function __toString()
    {
        return $this->getQueue();
    }
}
