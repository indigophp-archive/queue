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

    public function __construct(AMQPMessage $msg, RabbitConnector $connector)
    {
        $this->message   = $msg;
        $this->connector = $connector;
        $this->logger    = new NullLogger;
    }

    /**
     * {@inheritdoc}
     */
    public function delete()
    {
        $this->connector->getChannel()->basic_ack($this->message->delivery_info['delivery_tag']);
    }

    /**
     * {@inheritdoc}
     */
    public function bury()
    {
        $this->connector->getChannel()->queue_declare('buried', false, true, false, false);
        $this->connector->getChannel()->basic_publish($this->message, '', 'buried');
    }

    /**
     * {@inheritdoc}
     */
    public function release($delay = 0)
    {
        $this->connector->getChannel()->basic_nack($this->message->delivery_info['delivery_tag']);
    }

    /**
     * {@inheritdoc}
     */
    public function attempts()
    {
        return $this->message->delivery_info['redelivered'] ? 2 : 1;
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
