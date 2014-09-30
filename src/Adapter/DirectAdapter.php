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
use Indigo\Queue\Manager;
use Indigo\Queue\Message;
use Indigo\Queue\Exception\QueueEmptyException;

/**
 * Direct Adapter for running jobs immediately
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
class DirectAdapter extends AbstractAdapter
{
    /**
     * Last added message
     *
     * @var Message
     */
    protected $message;

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
    public function push($queue, Message $message)
    {
        $this->message = $message;

        $manager = $this->pop($queue);

        return $manager->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function pop($queue, $timeout = 0)
    {
        if ($this->message === null) {
            throw new QueueEmptyException($queue);
        }

        $payload = $this->message->createPayload();
        $this->message = null;

        return new $this->managerClass($queue, $payload, $this);
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
    public function delete(Manager $manager)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function clear($queue)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function release(Manager $manager, $delay = 0)
    {
        return true;
    }
}
