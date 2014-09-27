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

/**
 * Manages one job from the queue
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
interface Manager
{
    /**
    * Executes the job
    *
    * @return mixed Job return value
    */
    public function execute();

    /**
    * Returns the number of times the job has been attempted to execute
    *
    * @return integer
    */
    public function attempts();

    /**
     * Returns the payload
     *
     * @return []
     */
    public function getPayload();

    /**
     * Returns the queue name
     *
     * @return string
     */
    public function getQueue();

    /**
     * Returns the adapter
     *
     * @return Adapter
     */
    public function getAdapter();
}
