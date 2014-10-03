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

use Indigo\Queue\Message;
use Indigo\Queue\Exception\QueueEmptyException;
use IronMQ;
use stdClass;

/**
 * Iron Adapter
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
class IronAdapter extends AbstractAdapter
{
    /**
     * IronMQ object
     *
     * @var IronMQ
     */
    protected $iron;

    /**
     * @param IronMQ $iron
     */
    public function __construct(IronMQ $iron)
    {
        $this->iron = $iron;
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
        return $this->iron->postMessage(
            $message->getQueue(),
            json_encode($message->getData())
        );
    }

    /**
     * {@inheritdoc}
     */
    public function pop($queue, $timeout = 0)
    {
        $message = $this->iron->getMessage($queue, $timeout);

        if ($message instanceof stdClass) {
            return new $this->messageClass(
                $queue,
                json_decode($message->body, true),
                $message->id,
                $message->reserved_count
            );
        }

        throw new QueueEmptyException($queue);
    }

    /**
     * {@inheritdoc}
     */
    public function count($queue)
    {
        $stat = $this->iron->getQueue($queue);

        return (int) $stat->size;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Message $message)
    {
        $this->iron->deleteMessage($message->getQueue(), $message->getId());

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function clear($queue)
    {
        $this->iron->clearQueue($queue);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function release(Message $message)
    {
        $this->iron->releaseMessage(
            $message->getQueue(),
            $message->getId(),
            $this->getDelay($message)
        );

        return true;
    }

    /**
     * Returns the IronMQ object
     *
     * @return IronMQ
     */
    public function getIron()
    {
        return $this->iron;
    }

    /**
     * Sets the IronMQ object
     *
     * @param IronMQ $iron
     *
     * @return self
     */
    public function setIron(IronMQ $iron)
    {
        $this->iron = $iron;

        return $this;
    }
}
