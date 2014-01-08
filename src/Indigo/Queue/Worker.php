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

    public function __construct($queue, ConnectorInterface $connector)
    {
        if ($connector instanceof \Indigo\Queue\Connector\DirectConnector) {
            throw new \InvalidArgumentException('DirectConnector should not be used in a Worker');
        }

        $this->queue     = $queue;
        $this->connector = $connector;
        $this->logger    = new NullLogger;
    }

    /**
     * Listen to queue
     *
     * @param  float   $interval Sleep for certain time if no job is available
     * @param  integer $timeout  Wait timeout for pop
     */
    public function listen($interval = 5, $timeout = 0)
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
                $this->sleep($interval);
            }
        }
    }

    /**
     * Process one job from the queue
     *
     * @param  integer $timeout  Wait timeout for pop
     * @return mixed Job return value
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
     * Sleep for a certain time
     *
     * @param float $interval
     */
    protected function sleep($interval)
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

    /**
     * Sets a logger instance on the object
     *
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}
