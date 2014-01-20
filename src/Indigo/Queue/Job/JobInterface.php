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
    * Delete the job from the queue.
    */
    public function delete();

    /**
    * Bury the job for further inspection.
    */
    public function bury();

    /**
    * Release the job back into the queue.
    *
    * @param  int      $delay
    * @param  int|null $priority
    */
    public function release($delay = 0);

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
}
