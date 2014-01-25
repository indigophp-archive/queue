<?php

/*
 * This file is part of the Indigo Queue package.
 *
 * (c) IndigoPHP Development Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Indigo\Queue\Connector;

use Indigo\Queue\Job\JobInterface;

/**
 * Connector Inteface
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
interface ConnectorInterface
{
    /**
     * Checks whether connection is available
     *
     * @return boolean
     */
    public function isConnected();

    /**
    * Push a new job onto the queue
    *
    * @param  string $queue   Name of the queue
    * @param  array  $payload Array of payload
    * @param  array  $options Array of specific options
    * @return mixed
    */
    public function push($queue, array $payload, array $options = array());

    /**
    * Push a new job onto the queue after a delay
    *
    * @param  string $queue   Name of the queue
    * @param  int    $delay   Delay of job in seconds
    * @param  array  $payload Array of payload
    * @param  array  $options Array of specific options
    * @return mixed
    */
    public function delayed($queue, $delay, array $payload, array $options = array());

    /**
    * Pop the next job off of the queue
    *
    * @param  string       $queue   Name of the queue
    * @param  integer      $timeout Wait timeout
    * @return JobInterface
    */
    public function pop($queue, $timeout = 0);

    /**
     * Delete job from queue
     *
     * @param  JobInterface $job Job to delete
     * @return boolean      Always true
     */
    public function delete(JobInterface $job);

    /**
     * Release a job back to the queue
     *
     * @param  JobInterface $job   Job to release
     * @param  integer      $delay Delay the job with x seconds, 0 means no delay
     * @return boolean      Always true
     */
    public function release(JobInterface $job, $delay = 0);
}
