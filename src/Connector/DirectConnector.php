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

use Indigo\Queue\Connector;
use Indigo\Queue\Manager;
use Indigo\Queue\Job;
use Indigo\Queue\Exception\QueueEmptyException;

/**
 * Direct Connector for running jobs immediately
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
class DirectConnector extends AbstractConnector
{
    /**
     * Last added job
     *
     * @var Job
     */
    protected $job;

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
        $this->job = $job;

        $manager = $this->pop($queue);

        return $manager->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function delayed($queue, $delay, Job $job)
    {
        sleep($delay);

        return $this->push($queue, $job);
    }

    /**
     * {@inheritdoc}
     */
    public function pop($queue, $timeout = 0)
    {
        if ($this->job === null) {
            throw new QueueEmptyException($queue);
        }

        $payload = $this->job->createPayload();
        $this->job = null;

        return new $this->managerClass($queue, $payload, $this);
    }

    /**
     * {@inheritdoc}
     */
    public function count($queue)
    {
        return 1;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Manager $manager)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function clear($queue)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function release(Manager $manager, $delay = 0)
    {
        return true;
    }
}
