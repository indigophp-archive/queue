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
class Worker implements LoggerAwareInterface
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

    public function __construct(QueueInterface $queue, LoggerInterface $logger = null)
    {
        $this->setQueue($queue);
        is_null($logger) or $this->setLogger($logger);
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
                die('Memory usage (' . $memory . 'MB) reached');
            }
        }
    }

    /**
     * Process one job from the queue
     *
     * @return null
     */
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

    /**
     * Create new instance of a queue worker
     *
     * @param  mixed  $queue     Queue instance or queue name
     * @param  string $driver    Driver name
     * @param  mixed  $connector Array of connector data or connector object itself
     * @return new static
     */
    public static function forge($queue, $driver = null, $connector = null)
    {
        if ( ! $queue instanceof QueueInterface) {
            $queue = static::resolveQueue($queue, $driver, $connector)
        }

        return new static($queue);
    }

    /**
     * Resolve queue
     *
     * @param  string $queue     Queue name
     * @param  string $driver    Driver name
     * @param  mixed  $connector Array of connector data or connector object itself
     * @return QueueInterface
     */
    public static function resolveQueue($queue, $driver, $connector = null)
    {
        // Get driver class name and queue name
        $driver = 'Phresque\\Queue\\' . trim(ucfirst(strtolower($driver))) . 'Queue';
        $queue  = strtolower($queue);

        // Instantiate class
        return new $driver($queue, $connector);
    }
}
