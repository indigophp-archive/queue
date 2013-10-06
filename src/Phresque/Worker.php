<?php

namespace Phresque;

use Phresque\Queue\QueueInterface;
use Phresque\Connector\ConnectorInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

class Worker implements LoggerAwareInterface
{
    protected $queue;

    /**
     * Logger instance
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct($queue, $driver = null, $connection = array())
    {
        if ($queue instanceof QueueInterface) {
            $this->setQueue($queue);
        } else {
            $this->resolveQueue($queue, $driver, $connection);
        }
    }

    public function getQueue()
    {
        return $this->queue;
    }

    public function setQueue(QueueInterface $queue)
    {
        $this->queue = $queue;
        if (empty($this->logger)) {
            $logger = $queue->getLogger();

            if ($logger instanceof LoggerInterface) {
                $this->setLogger($logger);
            }
        }
    }

    public function resolveQueue($queue, $driver, $connection = array())
    {
        $driver = 'Phresque\\Queue\\' . trim(ucfirst(strtolower($driver))) . 'Queue';
        $queue  = strtolower($queue);

        $driver = new $driver($queue, $connection);
        $this->setQueue($driver);
    }

    public function pop()
    {
        return $this->queue->pop();
    }

    public function listen($delay, $memory, $timeout = 60)
    {
        while (true) {
            $job = $this->pop($delay, $memory, $timeout);
        }
    }

    /**
     * Gets a logger instance from the object
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