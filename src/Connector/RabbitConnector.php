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

use Indigo\Queue\Manager\ManagerInterface;
use Indigo\Queue\Job;
use Indigo\Queue\Exception\QueueEmptyException;
use PhpAmqpLib\Connection\AbstractConnection as AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Channel\AMQPChannel;

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
     * Creates a new RabbitConnector
     *
     * @param AMQPConnection $amqp
     * @param boolean        $persistent
     */
    public function __construct(AMQPConnection $amqp, $persistent = true)
    {
        $this->amqp       = $amqp;
        $this->persistent = $persistent;

        $this->regenerateChannel();

        parent::__construct();
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
        return $this->channel->is_open;
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
     * Returns a channel
     *
     * @return AMQPChannel
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * Regenerates a channel
     *
     * @return AMQPChannel Old channel
     */
    public function regenerateChannel()
    {
        $channel = $this->channel;
        $this->channel = $this->amqp->channel();

        return $channel;
    }

    /**
     * {@inheritdoc}
     */
    public function push($queue, Job $job)
    {
        $msg = $this->prepareMessage($queue, $job);

        return $this->channel->basic_publish($msg, '', $queue);
    }

    /**
     * {@inheritdoc}
     */
    public function delayed($queue, $delay, Job $job)
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

        $msg = $this->prepareMessage($queue, $job);

        return $this->channel->basic_publish($msg, '', reset($tmpQueue));
    }

    /**
     * Prepares a message
     *
     * @param string $queue
     * @param Job    $job
     *
     * @return AMQPMessage
     *
     * @codeCoverageIgnore
     */
    protected function prepareMessage($queue, Job $job)
    {
        $this->queueDeclare($queue);

        return new AMQPMessage(json_encode($job->createPayload()), $job->getOptions());
    }

    /**
     * Declares a new queue
     *
     * @param string $queue
     * @param []     $arguments
     *
     * @return mixed
     *
     * @codeCoverageIgnore
     */
    protected function queueDeclare($queue = '', array $arguments = [])
    {
        return $this->channel->queue_declare(
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
     * @param string $exchange
     * @param string $type
     *
     * @codeCoverageIgnore
     */
    protected function exchangeDeclare($exchange, $type = 'direct')
    {
        return $this->channel->exchange_declare(
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

        $message = $this->channel->basic_get($queue);

        // @codeCoverageIgnoreStart
        if ($timeout > 0) {
            $start = microtime(true);

            while (is_null($message) and $timeout > microtime(true) - $start) {
                sleep(1);
                $message = $this->channel->basic_get($queue);
            }
        }
        // @codeCoverageIgnoreEnd

        if ($message instanceof AMQPMessage) {
            return new $this->managerClass($queue, $message, $this);
        }

        throw new QueueEmptyException($queue);
    }

    /**
     * {@inheritdoc}
     */
    public function count($queue)
    {
        $queue = $this->queueDeclare($queue);

        return $queue[1];
    }

    /**
     * {@inheritdoc}
     */
    public function delete(ManagerInterface $manager)
    {
        $manager->getChannel()->basic_ack($manager->getMessage()->delivery_info['delivery_tag']);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function clear($queue)
    {
        $this->channel->queue_purge($queue);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function release(ManagerInterface $manager, $delay = 0)
    {
        $payload = $manager->getPayload();
        $payload['attempts'] = isset($payload['attempts']) ? $payload['attempts'] + 1 : 2;

        $this->delete($manager);

        $job = Job::createFromPayload($payload);

        if ($delay > 0) {
            $this->delayed($manager->getQueue(), $delay, $job);
        } else {
            $this->push($manager->getQueue(), $job);
        }

        return true;
    }
}
