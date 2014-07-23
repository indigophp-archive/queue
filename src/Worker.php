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
use Indigo\Queue\Job\JobInterface;
use Indigo\Queue\Connector\DirectConnector;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\NullLogger;
use Indigo\Queue\Exception\QueueEmptyException;
use Exception;
use InvalidArgumentException;

/**
 * Worker class
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 *
 * @codeCoverageIgnore
 */
class Worker
{
    use \Psr\Log\LoggerAwareTrait;

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
     * Config values
     *
     * @var []
     */
    protected static $config = [
        'retry'  => 0,
        'delay'  => 0,
        'delete' => false,
    ];

    /**
     * Creates a new Worker
     *
     * @param string             $queue
     * @param ConnectorInterface $connector
     */
    public function __construct($queue, ConnectorInterface $connector)
    {
        if ($connector instanceof DirectConnector) {
            throw new InvalidArgumentException('DirectConnector should not be used in a Worker');
        }

        $this->queue = $queue;

        $this->setConnector($connector)
            ->setLogger(new NullLogger);
    }

    /**
     * Listens to queue
     *
     * @param float   $interval Sleep for certain time if no job is available
     * @param integer $timeout  Wait timeout for pop
     */
    public function listen($interval = 5, $timeout = 0)
    {
        while (true) {
            // Process the current job if available
            // or sleep for a certain time
            if ($job = $this->getJob($timeout)) {
                $job->execute();
            } elseif ($interval > 0) {
                $this->sleep($interval);
            }
        }
    }

    /**
     * Processes one job from the queue
     *
     * @param integer $timeout Wait timeout for pop
     *
     * @return mixed Job return value
     */
    public function work($timeout = 0)
    {
        // Only run when valid job object returned
        if ($job = $this->getJob($timeout)) {
            return $job->execute();
        }
    }

    /**
     * Returns a ManagerInterface
     *
     * @param integer $timeout Wait timeout for pop
     *
     * @return ManagerInterface Returns null if $job is not a valid JobIterface
     */
    protected function getJob($timeout = 0)
    {
        try {
            $job = $this->connector->pop($this->queue, $timeout);
        } catch (QueueEmptyException $e) {
            return false;
        }

        if ($job instanceof LoggerAwareInterface) {
            $job->setLogger($this->logger);
        }

        return $job;
    }

    /**
     * Sleep for a certain time
     *
     * @param float $interval
     */
    protected function sleep($interval)
    {
        if ($interval < 1) {
            $interval = $interval / 1000000;
            usleep($interval);
        } else {
            sleep($interval);
        }
    }
}
