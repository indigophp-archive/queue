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
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Channel\AMQPChannel;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\Options;
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

    public function __construct($host, $port, $user, $password, array $options = array())
    {
        // Don't worry, Connection object injected
        if ($host instanceof AbstractConnection) {
            $this->amqp = $host;
        } else {
            $this->amqp = new AMQPStreamConnection($host, $port, $user, $password);
        }

        $this->channel = $this->amqp->channel();

        $this->setLogger(new NullLogger);
    }

    protected function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
                'port' => 5672,
                'vhost' => '/',
                'insist' => false,
                'login_method' => 'AMQPLAIN',
                'login_response' => null,
                'locale' => 'en_US',
                'connection_timeout' => 3,
                'read_write_timeout' => 3,
                'context' => null
            ));
    }

    public function __destruct()
    {
        $this->channel->close();
        $this->amqp->close();
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
        $this->channel->exchange_declare('delay', 'direct', false, true, false);
        $delay = $delay * 1000;

        $queue = $this->channel->queue_declare(
            '',
            false,
            true,
            false,
            false,
            false,
            array(
                'x-expires'                 => array('I', $delay + 2000),
                'x-message-ttl'             => array('I', $delay),
                'x-dead-letter-exchange'    => array('S', 'amq.direct'),
                'x-dead-letter-routing-key' => array('S', $payload['queue']),
            )
        );

        $this->channel->queue_bind($payload['queue'], 'delay', $payload['queue']);

        $msg = $this->prepareMessage($payload, $options);
        $this->channel->basic_publish($msg, '', reset($queue));
    }

    private function prepareMessage(array $payload, array $options = array())
    {
        if ($payload['queue'] == 'buried') {
            throw new \InvalidArgumentException('Do not push jobs directly to buried queue');
        }

        $resolver = new OptionsResolver();
        $resolver->setOptional(array_keys($this->messageOptions));
        $resolver->setAllowedTypes($this->messageOptions);
        $resolver->setDefaults(array(
            'delivery_mode' => 2,
        ));

        $options = $resolver->resolve($options);

        $this->channel->queue_declare($payload['queue'], false, true, false, false);

        return new AMQPMessage(json_encode($payload), $options);
    }

    /**
     * {@inheritdoc}
     */
    public function pop($queue, $timeout = 0)
    {
        $this->channel->queue_declare($queue, false, true, false, false);

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
}
