<?php
/*
 * This file is part of the Phresque package.
 *
 * (c) Márk Sági-Kazár <mark.sagikazar@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phresque\Queue;

/**
 * Queue Inteface
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
interface QueueInterface
{
    /**
     * Connect to backend
     *
     * @return void
     */
    public function connect($connector);

    /**
     * Checks whether connection is available
     *
     * @return boolean
     */
    public function isAvailable();

    /**
    * Push a new job onto the queue.
    *
    * @param  string $job
    * @param  mixed  $data
    * @return mixed
    */
    public function push($job, $data = null);

    /**
    * Push a new job onto the queue after a delay.
    *
    * @param  int    $delay
    * @param  string $job
    * @param  mixed  $data
    * @return mixed
    */
    public function delayed($delay, $job, $data = null);

    /**
    * Pop the next job off of the queue.
    *
    * @param  string $queue
    * @return Phresque\Job\Job|null
    */
    public function pop();
}