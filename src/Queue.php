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

use Jeremeamia\SuperClosure\SerializableClosure;

/**
 * Handles sending jobs to backend
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
class Queue
{
    /**
     * Adapter object
     *
     * @var Adapter
     */
    protected $adapter;

    /**
     * Queue name
     *
     * @var string
     */
    protected $queue;

    /**
     * Creates a new Queue
     *
     * @param string  $queue
     * @param Adapter $adapter
     */
    public function __construct($queue, Adapter $adapter)
    {
        $this->queue = $queue;

        $this->setAdapter($adapter);
    }

    /**
     * Returns the queue name
     *
     * @return string
     */
    public function getQueue()
    {
        return $this->queue;
    }

    /**
     * Returns the Adapter
     *
     * @return Adapter
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * Sets the Adapter
     *
     * @param Adapter $adapter
     *
     * @return self
     */
    public function setAdapter(Adapter $adapter)
    {
        $this->adapter = $adapter;

        return $this;
    }

    /**
    * Pushes a new job onto the queue
    *
    * @param Job $job
    *
    * @return mixed
    */
    public function push(Job $job)
    {
        return $this->adapter->push($this->queue, $job);
    }

    /**
     * Returns queue name
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getQueue();
    }
}
