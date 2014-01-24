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

    public function __construct(AMQPMessage $msg, RabbitConnector $connector)
    {
        $this->message   = $msg;
        $this->connector = $connector;
        $this->channel   = $connector->regenerateChannel();
        $this->setLogger(new NullLogger);
    }

    public function __destruct()
    {
        $this->channel->close();
    }

    /**
     * {@inheritdoc}
     */
    public function delete()
    {
        $this->channel->basic_ack($this->message->delivery_info['delivery_tag']);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function bury()
    {
        $this->delete();
        $this->channel->queue_declare('buried', false, true, false, false);
        $this->channel->basic_publish($this->message, '', 'buried');

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function release($delay = 0)
    {
        $payload = $this->getPayload();
        $payload['attempts'] = isset($payload['attempts']) ? $payload['attempts'] + 1 : 2;

        $this->delete();

        if ($delay > 0) {
            $this->connector->delayed($delay, $payload);
        } else {
            $this->connector->push($payload);
        }

        return true;
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
     * {@inheritdoc}
     * Get/Regenerate payload
     *
     * @param boolean $regenerate
     */
    public function getPayload($regenerate = false)
    {
        if ($regenerate === true or empty($this->payload)) {
            return $this->payload = json_decode($this->message->body, true);
        }

        return parent::getPayload();
    }
}
