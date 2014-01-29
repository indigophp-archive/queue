<?php

/*
 * This file is part of the Indigo Queue package.
 *
 * (c) Indigo Development Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Indigo\Queue\Connector;

use Indigo\Queue\Job\JobInterface;
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
    }

    /**
     * Resolve AMQPMessage options
     *
     * @param  array $options
     * @return array Resolved options
     */
    protected function resolveMessageOptions(array $options)
    {
        static $resolver;

        if (!$resolver instanceof OptionsResolver) {
            $resolver = new OptionsResolver;
            $this->setDefaultMessageOptions($resolver);
        }

        return $resolver->resolve($options);
    }

    /**
     * Set default message options
     *
     * @param OptionsResolverInterface $resolver
     */
    protected function setDefaultMessageOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setOptional(array_keys($this->messageOptions));
        $resolver->setAllowedTypes($this->messageOptions);
        $resolver->setDefaults(array(
            'delivery_mode' => 2,
        ));
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
    public function push($queue, array $payload, array $options = array())
    {
        $msg = $this->prepareMessage($queue, $payload, $options);
        $this->channel->basic_publish($msg, '', $queue);
    }

    /**
     * {@inheritdoc}
     */
    public function delayed($queue, $delay, array $payload, array $options = array())
    {
        $this->exchangeDeclare('delay');

        $delay = $delay * 1000;

        $tmpQueue = $this->queueDeclare(
            '',
            array(
                'x-expires'                 => array('I', $delay + 2000),
                'x-message-ttl'             => array('I', $delay),
                'x-dead-letter-exchange'    => array('S', 'delay'),
                'x-dead-letter-routing-key' => array('S', $queue),
            )
        );

        $this->channel->queue_bind($queue, 'delay', $queue);

        $msg = $this->prepareMessage($queue, $payload, $options);
        $this->channel->basic_publish($msg, '', $tmpQueue);
    }

    private function prepareMessage($queue, array $payload, array $options = array())
    {
        $options = $this->resolveMessageOptions($options);

        $this->queueDeclare($queue);

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

        $job = $this->channel->basic_get($queue);
        if ($timeout > 0) {
            $start = microtime(true);

            while (is_null($job) and $timeout > microtime(true) - $start) {
                sleep(1);
                $job = $this->channel->basic_get($queue);
            }
        }

        if ($job instanceof AMQPMessage) {
            return new RabbitJob($queue, $job, $this);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function delete(JobInterface $job)
    {
        $job->getChannel()->basic_ack($job->getMessage()->delivery_info['delivery_tag']);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function release(JobInterface $job, $delay = 0)
    {
        $payload = $job->getPayload();
        $payload['attempts'] = isset($payload['attempts']) ? $payload['attempts'] + 1 : 2;

        $this->delete($job);

        if ($delay > 0) {
            $this->delayed($job->getQueue(), $delay, $payload);
        } else {
            $this->push($job->getQueue(), $payload);
        }

        return true;
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

    /**
     * Regenerate channel
     *
     * @return AMQPChannel Old channel
     */
    public function regenerateChannel()
    {
        $channel = $this->channel;
        $this->channel = $this->amqp->channel();

        return $channel;
    }
}
