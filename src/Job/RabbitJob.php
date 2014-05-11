<?php

/*
 * This file is part of the Indigo Queue package.
 *
 * (c) Indigo Development Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Indigo\Queue\Job;

use Indigo\Queue\Connector\RabbitConnector;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\NullLogger;

/**
 * Rabbit Job
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
class RabbitJob extends AbstractJob
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
     * @codeCoverageIgnore
     */
    public function __construct(AMQPMessage $message, RabbitConnector $connector)
    {
        $this->message   = $message;
        $this->connector = $connector;
        $this->channel   = $connector->regenerateChannel();
        $this->logger    = new NullLogger;

        $this->setPayload(json_decode($message->body, true));
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
     * Get message
     *
     * @return AMQPMessage
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Get channel
     *
     * @return AMQPChannel
     */
    public function getChannel()
    {
        return $this->channel;
    }
}
