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

use Indigo\Queue\Manager;
use Indigo\Queue\Message;

/**
 * Implements connection details
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
interface Adapter
{
    /**
     * Checks whether connection is available
     *
     * @return boolean
     */
    public function isConnected();

    /**
    * Pushes a new message onto the queue
    *
    * @param string  $queue
    * @param Message $message
    *
    * @return mixed
    */
    public function push($queue, Message $message);

    /**
    * Pops the next job off of the queue
    *
    * @param string  $queue   Name of the queue
    * @param integer $timeout Wait timeout
    *
    * @return Manager
    *
    * @throws QueueEmptyException If no job can be returned
    */
    public function pop($queue, $timeout = 0);

    /**
     * Returns the count of jobs
     *
     * @param string $queue
     *
     * @return integer
     */
    public function count($queue);

    /**
     * Deletes a job from queue
     *
     * @param Manager $manager
     *
     * @return boolean Always true
     */
    public function delete(Manager $manager);

    /**
     * Clears the queue
     *
     * @param string $queue
     *
     * @return boolean Always true
     */
    public function clear($queue);

    /**
     * Releases a job back to the queue
     *
     * @param Manager $manager
     * @param integer $delay   Delay the job with x seconds, 0 means no delay
     *
     * @return boolean Always true
     */
    public function release(Manager $manager, $delay = 0);
}
