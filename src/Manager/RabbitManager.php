<?php

/*
 * This file is part of the Indigo Queue package.
 *
 * (c) Indigo Development Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Indigo\Queue\Manager;

use Indigo\Queue\Connector\RabbitConnector;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\NullLogger;

/**
 * Rabbit Manager
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
class RabbitManager extends AbstractManager
{
    /**
     * AMQPMessage
     *
     * @var AMQPMessage
     */
    protected $message;

    /**
     * Channel
     *
     * @var AMQPChannel
     */
    protected $channel;

    /**
     * Creates a new RabbitManager
     *
     * @param string          $queue
     * @param AMQPMessage     $message
     * @param RabbitConnector $connector
     */
    public function __construct($queue, AMQPMessage $message, RabbitConnector $connector)
    {
        $this->channel = $connector->regenerateChannel();
        $this->message = $message;
        $this->payload = json_decode($message->body, true);

        parent::__construct($queue, $connector);
    }

    /**
     * {@inheritdoc}
     */
    public function attempts()
    {
        $payload = $this->getPayload();

        return isset($payload['attempts']) ? $payload['attempts'] : 1;
    }

    /**
     * Returns the message
     *
     * @return AMQPMessage
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Returns the channel
     *
     * @return AMQPChannel
     */
    public function getChannel()
    {
        return $this->channel;
    }
}
