<?php

namespace Phresque;

use Phresque\Queue\QueueInterface;
use Phresque\Job\JobInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

class Worker
{
    protected $queue;

    /**
     * Logger instance
     *
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct($queue, $driver = null, $connector = null)
    {
        if ($queue instanceof QueueInterface) {
            $this->setQueue($queue);
        } else {
            $this->resolveQueue($queue, $driver, $connector);
        }
    }

    /**
     * Get current queue
     *
     * @return QueueInterface
     */
    public function getQueue()
    {
        return $this->queue;
    }

    /**
     * Set queue
     *
     * @param QueueInterface $queue
     */
    public function setQueue(QueueInterface $queue)
    {
        $this->queue = $queue;
    }

    /**
     * Resolve queue
     *
     * @param  string $queue     Queue name
     * @param  string $driver    Driver name
     * @param  mixed  $connector Array of connector data or connector object itself
     * @return null
     */
    public function resolveQueue($queue, $driver, $connector = null)
    {
        $driver = 'Phresque\\Queue\\' . trim(ucfirst(strtolower($driver))) . 'Queue';
        $queue  = strtolower($queue);

        $driver = new $driver($queue, $connector);
        $this->setQueue($driver);
    }

    /**
     * Listen for queue
     *
     * @param  integer $memory Max memory allowed for a worker
     * @return null
     */
    public function listen($memory = null)
    {
        while (true) {
            $this->work();

            if ( ! is_null($memory) and (memory_get_usage() / 1024 / 1024) > $memory) {
                die;
            }
        }
    }

    public function work()
    {
        $job = $this->queue->pop();
        if ($job instanceof JobInterface) {
            $job->execute();
        }
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
