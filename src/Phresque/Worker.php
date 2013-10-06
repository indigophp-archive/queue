<?php

namespace Phresque;

use Phresque\Queue\QueueInterface;
use Phresque\Connector\BeanstalkdConnector;
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

    public function __construct($queue, $driver = null, $connector = null)
    {
        if ($queue instanceof QueueInterface) {
            $this->setQueue($queue, true);
        } else {
            $this->resolveQueue($queue, $driver, $connector);
        }
    }

    public function getQueue()
    {
        return $this->queue;
    }

    public function setQueue(QueueInterface $queue, $setLogger = false)
    {
        $this->queue = $queue;
        if ($setLogger) {
            $logger = $queue->getLogger();

            if ($logger instanceof LoggerInterface) {
                $this->setLogger($logger);
            }
        }
    }

    public function resolveQueue($queue, $driver, $connector = null)
    {
        $driver = 'Phresque\\Queue\\' . trim(ucfirst(strtolower($driver))) . 'Queue';
        $queue  = strtolower($queue);

        $driver = new $driver($queue, $connector);
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