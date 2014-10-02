<?php

/*
 * This file is part of the Indigo Queue package.
 *
 * (c) Indigo Development Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Indigo\Queue\Adapter;

use Indigo\Queue\Adapter;
use Indigo\Queue\Message;
use Indigo\Queue\Exception\QueueEmptyException;
use Indigo\Queue\Worker;
use LogicException;

/**
 * Direct Adapter for running jobs immediately
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
class DirectAdapter implements Adapter
{
    /**
     * Last added message
     *
     * @var Message
     */
    private $message;

    /**
     * Worker instance
     *
     * @var Worker
     */
    private $worker;

    /**
     * @param Worker $worker
     */
    public function __construct(Worker $worker)
    {
        // Queue and adapter should be consistent
        $this->worker = $worker;
    }

    /**
     * {@inheritdoc}
     */
    public function isConnected()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function push(Message $message)
    {
        $this->message = $message;

        return $this->worker->work();
    }

    /**
     * {@inheritdoc}
     */
    public function pop($queue, $timeout = 0)
    {
        if (is_null($this->message)) {
            throw new QueueEmptyException($queue);
        }

        $message = $this->message;

        $this->message = null;

        return $message;
    }

    /**
     * {@inheritdoc}
     */
    public function count($queue)
    {
        return 1;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Message $message)
    {
        $this->message = null;

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function clear($queue)
    {
        $this->message = null;

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function release(Message $message)
    {
        return true;
    }
}
