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
use Indigo\Queue\Job\JobInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Worker class
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
class Worker implements LoggerAwareInterface
{
    /**
     * Queue name
     *
     * @var string
     */
    protected $queue;

    /**
     * Connector
     *
     * @var ConnectorInterface
     */
    protected $connector;

    /**
     * Logger instance
     *
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct($queue, ConnectorInterface $connector, LoggerInterface $logger = null)
    {
        if ($connector instanceof \Indigo\Queue\Connector\DirectConnector) {
            throw new \InvalidArgumentException('DirectConnector should not be used in a Worker');
        }

        $this->queue = $queue;
        $this->connector = $connector;

        is_null($logger) and $logger = new NullLogger;
        $this->setLogger($logger);
    }

    /**
     * Listen to queue
     *
     * @param  integer $interval Sleep for certain time if no job is available
     * @param  integer $memory   Max memory allowed for a worker
     * @param  integer $timeout  Wait timeout for pop
     */
    public function listen($interval = 5, $memory = null, $timeout = 0)
    {
        while (true) {
            // Pop job from the queue
            $job = $this->connector->pop($this->queue, $timeout);

            // Process the current job if available
            // or (u)sleep for a certain time
            if ($job instanceof JobInterface) {
                $job->setLogger($this->logger);
                $job->execute();
            }
            elseif($interval > 0)
            {
                if ($interval < 1)
                {
                    $interval = $interval / 1000000;
                    usleep($interval);
                }
                else
                {
                    sleep($interval);
                }
            }

            // Check whether max memory reached
            if ( ! is_null($memory) and (memory_get_usage() / 1024 / 1024) > $memory) {
                $this->logger->info('Memory usage limit (' . $memory . 'MB) reached, worker is going to die.');
                die('Memory usage (' . $memory . 'MB) reached');
            }
        }
    }

    /**
     * Process one job from the queue
     */
    public function work($timeout = 0)
    {
        // Pop job from the queue
        $job = $this->connector->pop($this->queue, $timeout);

        // Only run when valid job object returned
        if ($job instanceof JobInterface) {
            $job->setLogger($this->logger);
            return $job->execute();
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
