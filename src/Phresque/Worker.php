<?php
/*
 * This file is part of the Phresque package.
 *
 * (c) Márk Sági-Kazár <mark.sagikazar@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phresque;

use Phresque\Queue\QueueInterface;
use Phresque\Job\JobInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

/**
 * Worker class
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
class Worker
{
    /**
     * Current queue object
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
        // Get driver class name and queue name
        $driver = 'Phresque\\Queue\\' . trim(ucfirst(strtolower($driver))) . 'Queue';
        $queue  = strtolower($queue);

        // Instantiate class
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
            // Process the current job if available
            $this->work();

            // Check whether max memory reached
            if ( ! is_null($memory) and (memory_get_usage() / 1024 / 1024) > $memory) {
                die;
            }
        }
    }

    public function work()
    {
        // Pop job from the queue
        $job = $this->queue->pop();

        // Only run when valid job object returned
        if ($job instanceof JobInterface) {
            $job->setLogger($this->logger);
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
