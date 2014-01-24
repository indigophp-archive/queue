<?php

/*
 * This file is part of the Indigo Queue package.
 *
 * (c) IndigoPHP Development Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Indigo\Queue\Connector;

use Indigo\Queue\Job\RabbitJob;
use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Channel\AMQPChannel;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Psr\Log\NullLogger;

/**
 * Rabbit connector
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
class RabbitConnector extends AbstractConnector
{
    /**
     * AMQP object
     *
     * @var AbstractConnection
     */
    protected $amqp;

    /**
     * Channel object
     *
     * @var AMQPChannel
     */
    protected $channel;

    /**
     * Use persistent queues and exchanges
     *
     * @var boolean
     */
    protected $persistent = true;

    /**
     * Message options
     *
     * @var array
     */
    protected $messageOptions = array(
        "content_type"        => "string",
        "content_encoding"    => "string",
        "application_headers" => "array",
        "delivery_mode"       => "integer",
        "priority"            => "integer",
        "correlation_id"      => "string",
        "reply_to"            => "string",
        "expiration"          => "string",
        "message_id"          => "string",
        "timestamp"           => "string",
        "type"                => "string",
        "user_id"             => "string",
        "app_id"              => "string",
        "cluster_id"          => "string"
    );

    /**
     * Message resolver
     *
     * @var OptionsResolverInterface
     */
    protected $messageResolver;

    public function __construct(AbstractConnection $amqp, $persistent = true)
    {
        $this->amqp = $amqp;

        $this->regenerateChannel();

        $this->setLogger(new NullLogger);

        $this->persistent = $persistent;

        $this->setMessageResolver(new OptionsResolver());
    }

    /**
     * Set AMQPMessage defaults
     *
     * @param OptionsResolverInterface $resolver
     */
    protected function setMessageResolver(OptionsResolverInterface $resolver)
    {
        $resolver->setOptional(array_keys($this->messageOptions));
        $resolver->setAllowedTypes($this->messageOptions);
        $resolver->setDefaults(array(
            'delivery_mode' => 2,
        ));

        return $this->messageResolver = $resolver;
    }

    /**
     * Check whether persistent queues and exchanges are used
     *
     * @return boolean
     */
    public function isPersistent()
    {
        return $this->persistent;
    }

    public function __destruct()
    {
        if ($this->isConnected()) {
            // $this->channel->close();
            // $this->amqp->close();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isConnected()
    {
        return $this->channel->is_open;
    }

    /**
     * {@inheritdoc}
     */
    public function push(array $payload, array $options = array())
    {
        $msg = $this->prepareMessage($payload, $options);
        $this->channel->basic_publish($msg, '', $payload['queue']);
    }

    /**
     * {@inheritdoc}
     */
    public function delayed($delay, array $payload, array $options = array())
    {
        $this->exchangeDeclare('delay');

        $delay = $delay * 1000;

        $queue = $this->queueDeclare(
            '',
            array(
                'x-expires'                 => array('I', $delay + 2000),
                'x-message-ttl'             => array('I', $delay),
                'x-dead-letter-exchange'    => array('S', 'delay'),
                'x-dead-letter-routing-key' => array('S', $payload['queue']),
            )
        );

        $this->channel->queue_bind($payload['queue'], 'delay', $payload['queue']);

        $msg = $this->prepareMessage($payload, $options);
        $this->channel->basic_publish($msg, '', $queue);
    }

    private function prepareMessage(array $payload, array $options = array())
    {
        if ($payload['queue'] == 'buried') {
            throw new \InvalidArgumentException('Do not push jobs directly to buried queue');
        }

        $options = $this->messageResolver->resolve($options);

        $this->queueDeclare($payload['queue']);

        return new AMQPMessage(json_encode($payload), $options);
    }

    public function queueDeclare($queue = '', array $arguments = array())
    {
        $queue = $this->channel->queue_declare(
            $queue,
            false,
            $this->persistent,
            false,
            false,
            false,
            $arguments
        );

        return reset($queue);
    }

    protected function exchangeDeclare($exchange, $type = 'direct')
    {
        $this->channel->exchange_declare(
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
    public function pop($queue, $timeout = 0)
    {
        $this->queueDeclare($queue);

        $msg = $this->channel->basic_get($queue);
        if ($timeout > 0) {
            $start = microtime(true);

            while (is_null($msg) and $timeout > microtime(true) - $start) {
                sleep(1);
                $msg = $this->channel->basic_get($queue);
            }
        }

        if ($msg instanceof AMQPMessage) {
            return new RabbitJob($msg, $this);
        }
    }

    /**
     * Return AMQP object
     *
     * @return AbstractConnection
     */
    public function getAMQP()
    {
        return $this->amqp;
    }

    /**
     * Set AMQP object
     *
     * @param  AbstractConnection $amqp
     * @return RabbitConnector
     */
    public function setAMQP(AbstractConnection $amqp)
    {
        $this->amqp = $amqp;

        return $this;
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

    public function regenerateChannel()
    {
        $channel = $this->channel;
        $this->channel = $this->amqp->channel();

        return $channel;
    }
}
