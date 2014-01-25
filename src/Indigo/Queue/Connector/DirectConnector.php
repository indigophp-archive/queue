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

use Indigo\Queue\Job\JobInterface;
use Indigo\Queue\Job\DirectJob;
use Psr\Log\NullLogger;

/**
 * Direct driver for running jobs immediately
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
class DirectConnector extends AbstractConnector
{
    public function __construct()
    {
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
    public function push($queue, array $payload = array(), array $options = array())
    {
        $job = $this->pop($queue, null, $payload);

        $job->execute();

        return $job;
    }

    /**
     * {@inheritdoc}
     */
    public function delayed($queue, $delay, array $payload = array(), array $options = array())
    {
        sleep($delay);

        return $this->push($queue, $payload, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function pop($queue, $timeout = 0, array $payload = array())
    {
        $job = new DirectJob($payload, $this);
        $job->setQueue($queue);

        return $job;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(JobInterface $job)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function release(JobInterface $job, $delay = 0)
    {
        return true;
    }
}
