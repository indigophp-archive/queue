<?php
/*
 * This file is part of the Phresque package.
 *
 * (c) Márk Sági-Kazár <mark.sagikazar@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phresque\Queue;

use Closure;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

/**
 * Abstract queue class
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
abstract class AbstractQueue implements QueueInterface
{
    /**
     * Object holding Connector
     *
     * @var ConnectorInterface
     */
    protected $connector;

    /**
     * Queue name
     *
     * @var QueueInterface
     */
    protected $queue;

    /**
     * Logger instance
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
    * Create serialized payload
    *
    * @param  string $job
    * @param  mixed  $data
    * @return string
    */
    protected function createPayload($job, $data = null)
    {
        // Create special payload if it is a Closure
        if ($job instanceof Closure) {
            $payload = $this->createClosurePayload($job, $data);
        } else {
            $payload = array('job' => $job, 'data' => $data);
        }

        return json_encode($payload);
    }

    public function createClosurePayload($job, $data = null)
    {
        throw new \Exception('Pushing closures on a queue is not yet implemented');
    }

    public function getConnector()
    {
        return $this->connector;
    }

    public function setConnector($connector)
    {
        $this->connector = $connector;
    }

    /**
     * Gets a queue instance from the object
     *
     * @return QueueInterface
     */
    public function getQueue()
    {
        return $this->queue;
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