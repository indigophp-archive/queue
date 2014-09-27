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

use Indigo\Queue\Adapter;
use Indigo\Queue\Manager;
use Indigo\Queue\Job;
use Indigo\Queue\Exception\QueueEmptyException;

/**
 * Iron Adapter
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
class IronAdapter extends AbstractAdapter
{
    /**
     * IronMQ object
     *
     * @var IronMQ
     */
    protected $iron;

    /**
     * Creates a new IronAdapter
     *
     * @param IronMQ $iron
     */
    public function __construct(\IronMQ $iron)
    {
        $this->iron = $iron;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    public function isConnected()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function push($queue, Job $job)
    {
        return $this->iron->postMessage(
            $queue,
            json_encode($job->createPayload()),
            $job->getOptions()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function delayed($queue, $delay, Job $job)
    {
        $options = $job->getOptions();

        $options['delay'] = $delay;

        $job->setOptions($options);

        return $this->push($queue, $job);
    }

    /**
     * {@inheritdoc}
     */
    public function pop($queue, $timeout = 0)
    {
        $job = $this->iron->getMessage($queue, $timeout);

        if ($job instanceof \stdClass) {
            return new $this->managerClass($queue, $job, $this);
        }

        throw new QueueEmptyException($queue);
    }

    /**
     * {@inheritdoc}
     */
    public function count($queue)
    {
        $stat = $this->iron->getQueue($queue);

        return (int) $stat->size;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Manager $manager)
    {
        $this->iron->deleteMessage($manager->getQueue(), $manager->getIronJob()->id);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function clear($queue)
    {
        $this->iron->clearQueue($queue);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function release(Manager $manager, $delay = 0)
    {
        $this->iron->releaseMessage($manager->getQueue(), $manager->getIronJob()->id, $delay);

        return true;
    }

    /**
     * Returns the IronMQ object
     *
     * @return IronMQ
     */
    public function getIron()
    {
        return $this->iron;
    }

    /**
     * Sets the IronMQ object
     *
     * @param IronMQ $iron
     *
     * @return this
     */
    public function setIron(\IronMQ $iron)
    {
        $this->iron = $iron;

        return $this;
    }
}
