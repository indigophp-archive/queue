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
use PhpAmqpLib\Connection\AbstractConnection as AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Channel\AMQPChannel;

/**
 * Rabbit Adapter
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
class RabbitAdapter extends AbstractAdapter
{
    /**
     * AMQP object
     *
     * @var AbstractConnection
     */
    protected $amqp;

    /**
     * Channel objects (one for each queue)
     *
     * @var AMQPChannel[]
     */
    protected $channels = [];

    /**
     * Use persistent queues and exchanges
     *
     * @var boolean
     */
    protected $persistent = true;

    /**
     * Creates a new RabbitAdapter
     *
     * @param AMQPConnection $amqp
     * @param boolean        $persistent
     */
    public function __construct(AMQPConnection $amqp, $persistent = true)
    {
        $this->amqp = $amqp;
        $this->persistent = $persistent;
    }

    /**
     * Checks whether persistent queues and exchanges are used
     *
     * @return boolean
     */
    public function isPersistent()
    {
        return $this->persistent;
    }

    /**
     * {@inheritdoc}
     */
    public function isConnected()
    {
        if (empty($this->channels)) {
            return false;
        }

        return reset($this->channels)->is_open;
    }

    /**
     * Returns the AMQP object
     *
     * @return AMQPConnection
     */
    public function getAMQP()
    {
        return $this->amqp;
    }

    /**
     * Sets AMQP object
     *
     * @param AMQPConnection $amqp
     *
     * @return this
     */
    public function setAMQP(AMQPConnection $amqp)
    {
        $this->amqp = $amqp;

        return $this;
    }

    /**
     * Returns a channel for a queue
     *
     * @return AMQPChannel
     */
    public function getChannel($queue)
    {
        if (array_key_exists($queue, $this->channels)) {
            return $this->channels[$queue];
        }

        $channel = $this->amqp->channel();

        $this->queueDeclare($channel, $queue);
        $this->exchangeDeclare($channel, $queue);

        return $this->channels[$queue] = $channel;
    }

    /**
     * Declares a new queue
     *
     * @param AMQPChannel $channel
     * @param string      $queue
     * @param []          $arguments
     *
     * @return mixed
     *
     * @codeCoverageIgnore
     */
    protected function queueDeclare(AMQPChannel $channel, $queue = '', array $arguments = [])
    {
        return $channel->queue_declare(
            $queue,
            false,
            $this->persistent,
            false,
            false,
            false,
            $arguments
        );
    }

    /**
     * Declares a new exchange
     *
     * @param AMQPChannel $channel
     * @param string      $exchange
     * @param string      $type
     *
     * @codeCoverageIgnore
     */
    protected function exchangeDeclare(AMQPChannel $channel, $exchange, $type = 'direct')
    {
        return $channel->exchange_declare(
            $exchange,
            $type,
            false,
            $this->persistent,
            false
        );
    }

    /**
     * {@inheritdoc}
     */
    public function push(Message $message)
    {
        $queue = $message->getQueue();
        $channel = $this->getChannel($queue);
        $amqpMessage = new AMQPMessage(json_encode($message->getData()));

        return $channel->basic_publish($amqpMessage, '', $queue);
    }

    /**
     * {@inheritdoc}
     */
    public function pop($queue, $timeout = 0)
    {
        $channel = $this->getChannel($queue);

        $message = $channel->basic_get($queue);

        // @codeCoverageIgnoreStart
        if ($timeout > 0) {
            $start = microtime(true);

            while (is_null($message) and $timeout > microtime(true) - $start) {
                sleep(1);
                $message = $channel->basic_get($queue);
            }
        }
        // @codeCoverageIgnoreEnd

        if ($message instanceof AMQPMessage) {
            return new $this->messageClass(
                $queue,
                json_decode($message->body, true),
                $message->delivery_info['delivery_tag'],
                1 // TODO
            );
        }

        throw new QueueEmptyException($queue);
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
        $this->getChannel($message->getQueue())->basic_ack($message->getId());

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function clear($queue)
    {
        $this->getChannel($queue)->queue_purge($queue);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function release(Message $message)
    {
        $this->delete($message);

        return true;
    }
}
