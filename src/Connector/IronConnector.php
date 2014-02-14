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
use Indigo\Queue\Job\IronJob;
use IronMQ;
use Psr\Log\NullLogger;
use stdClass;

/**
 * Iron connector
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
class IronConnector extends AbstractConnector
{
    /**
     * IronMQ object
     *
     * @var IronMQ
     */
    protected $iron = null;

    public function __construct(IronMQ $iron)
    {
        $this->iron = $iron;

        $this->setLogger(new NullLogger);
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
    public function push($queue, array $payload, array $options = array())
    {
        $options = $this->resolveJobOptions($options);

        return $this->iron->postMessage(
            $queue,
            json_encode($payload),
            $options
        );
    }

    /**
     * {@inheritdoc}
     */
    public function delayed($queue, $delay, array $payload, array $options = array())
    {
        $options['delay'] = $delay;

        return $this->push($queue, $payload, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function pop($queue, $timeout = 0)
    {
        $job = $this->iron->getMessage($queue, $timeout);

        if ($job instanceof stdClass) {
            $job = new IronJob($job, $this);
            $job->setQueue($queue);

            return $job;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function delete(JobInterface $job)
    {
        $this->iron->deleteMessage($job->getQueue(), $job->getIronJob()->id);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function release(JobInterface $job, $delay = 0)
    {
        $this->iron->releaseMessage($job->getQueue(), $job->getIronJob()->id, $delay);

        return true;
    }

    /**
     * Return IronMQ object
     *
     * @return IronMQ
     */
    public function getIron()
    {
        return $this->iron;
    }

    /**
     * Set IronMQ object
     *
     * @param  IronMQ        $iron
     * @return IronConnector
     */
    public function setIron(IronMQ $iron)
    {
        $this->iron = $iron;

        return $this;
    }
}
