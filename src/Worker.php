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

use Indigo\Queue\Adapter\DirectAdapter;
use Indigo\Queue\Exception\JobNotFoundException;
use Indigo\Queue\Exception\QueueEmptyException;

/**
 * Worker class
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 *
 * @codeCoverageIgnore
 */
class Worker
{
    /**
     * Queue name
     *
     * @var string
     */
    protected $queue;

    /**
     * Adapter
     *
     * @var Adapter
     */
    protected $adapter;

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
     * @param string  $queue
     * @param Adapter $adapter
     */
    public function __construct($queue, Adapter $adapter)
    {
        if ($adapter instanceof DirectAdapter) {
            throw new \InvalidArgumentException('DirectAdapter should not be used in a Worker');
        }

        $this->queue = $queue;
        $this->adapter = $adapter;
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
            if ($manager = $this->getManager($timeout)) {
                $manager->execute();
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
        if ($manager = $this->getManager($timeout)) {
            return $manager->execute();
        }
    }

    /**
     * Returns a Manager
     *
     * @param integer $timeout Wait timeout for pop
     *
     * @return Manager Returns null if $manager is not a valid ManagerIterface
     */
    protected function getManager($timeout = 0)
    {
        try {
            $manager = $this->adapter->pop($this->queue, $timeout);
        } catch (JobNotFoundException $e) {
            $this->adapter->delete($manager);

            return false;
        } catch (QueueEmptyException $e) {
            return false;
        }

        return $manager;
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
