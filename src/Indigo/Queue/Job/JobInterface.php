<?php

/*
 * This file is part of the Indigo Queue package.
 *
 * (c) IndigoPHP Development Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Indigo\Queue\Job;

/**
 * Job Inteface
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
interface JobInterface
{
    /**
    * Execute the job.
    *
    * @return mixed Job return value
    */
    public function execute();

    /**
    * Get the number of times the job has been attempted to execute.
    *
    * @return integer
    */
    public function attempts();

    /**
     * Return the payload
     *
     * @return array Payload array
     */
    public function getPayload();

    /**
     * Set the payload
     *
     * @param  array        $payload Payload array
     * @return JobInterface
     */
    public function setPayload(array $payload);

    /**
     * Get the queue name
     *
     * @return string
     */
    public function getQueue();

    /**
     * Set the queue name
     *
     * @param  string       $queue
     * @return JobInterface
     */
    public function setQueue($queue);
}
