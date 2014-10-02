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
use Pheanstalk\Job as PheanstalkJob;
use Pheanstalk\PheanstalkInterface;
use Pheanstalk\Exception\ServerException;

/**
 * Beanstalkd Adapter
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
class BeanstalkdAdapter extends AbstractAdapter
{
    /**
     * {@inheritdoc}
     */
    protected $options = [
        'delay'    => PheanstalkInterface::DEFAULT_DELAY,
        'timeout'  => PheanstalkInterface::DEFAULT_TTR,
        'priority' => PheanstalkInterface::DEFAULT_PRIORITY,
    ];

    /**
     * Pheanstalk object
     *
     * @var PheanstalkInterface
     */
    protected $pheanstalk;

    /**
     * Creates a new BeanstalkdAdapter
     *
     * @param PheanstalkInterface $pheanstalk
     */
    public function __construct(PheanstalkInterface $pheanstalk)
    {
        $this->pheanstalk = $pheanstalk;
    }

    /**
     * Returns the Pheanstalk object
     *
     * @return PheanstalkInterface
     */
    public function getPheanstalk()
    {
        return $this->pheanstalk;
    }

    /**
     * Sets the Pheanstalk object
     *
     * @param PheanstalkInterface $pheanstalk
     *
     * @return self
     */
    public function setPheanstalk(PheanstalkInterface $pheanstalk)
    {
        $this->pheanstalk = $pheanstalk;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isConnected()
    {
        return $this->pheanstalk->getConnection()->isServiceListening();
    }

    /**
     * {@inheritdoc}
     */
    public function push(Message $message)
    {
        $arguments = [
            $message->getQueue(),
            json_encode($message->getData()),
            $this->getPriority($message),
            $this->getDelay($message),
        ];

        return call_user_func_array([$this->pheanstalk, 'putInTube'], $arguments);
    }

    /**
     * Returns the message priority
     *
     * @param Message $message
     *
     * @return integer
     */
    protected function getPriority(Message $message)
    {
        if ($message instanceof Priority) {
            return $message->getPriority();
        }

        return PheanstalkInterface::DEFAULT_PRIORITY;
    }

    /**
     * {@inheritdoc}
     */
    public function pop($queue, $timeout = 0)
    {
        $message = $this->pheanstalk->reserveFromTube($queue, $timeout);

        if ($message instanceof PheanstalkJob) {
            $stats = $this->pheanstalk->statsJob($message);

            return new $this->messageClass(
                $queue,
                json_decode($message->getData(), true),
                $message->getId(),
                (int) $stats->reserves
            );
        }

        throw new QueueEmptyException($queue);
    }

    /**
     * {@inheritdoc}
     */
    public function count($queue)
    {
        $stats = $this->pheanstalk->statsTube($queue);

        return $stats['current-jobs-ready'];
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Message $message)
    {
        $this->pheanstalk->delete($message);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function clear($queue)
    {
        $this->doClear($queue, 'ready');
        $this->doClear($queue, 'buried');
        $this->doClear($queue, 'delayed');

        return true;
    }

    /**
     * Clears a specific state
     *
     * @param string $queue
     * @param string $state
     *
     * @return boolean
     *
     * @codeCoverageIgnore
     */
    protected function doClear($queue, $state)
    {
        try {
            while ($item = $this->pheanstalk->{'peek'.ucfirst($state)}($queue)) {
                $this->pheanstalk->delete($item);
            }
        } catch (ServerException $e) {
            // There is no more items in the queue
        }

        return true;
    }

    /**
     * Bury the job
     *
     * @param Message $message
     *
     * @return boolean Always true
     */
    public function bury(Message $message)
    {
        $this->pheanstalk->bury($message);

        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @param integer|null $priority
     */
    public function release(Message $message)
    {
        $this->pheanstalk->release(
            $message,
            $this->getPriority($message),
            $this->getDelay($message)
        );

        return true;
    }
}
