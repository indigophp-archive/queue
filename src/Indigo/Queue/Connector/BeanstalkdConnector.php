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
use Indigo\Queue\Job\BeanstalkdJob;
use Pheanstalk_Job;
use Pheanstalk_Pheanstalk as Pheanstalk;
use Pheanstalk_PheanstalkInterface as PheanstalkInterface;
use Psr\Log\NullLogger;

/**
 * Beanstalkd connector
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
class BeanstalkdConnector extends AbstractConnector
{
    /**
     * Pheanstalk object
     *
     * @var Pheanstalk
     */
    protected $pheanstalk = null;

    public function __construct(PheanstalkInterface $pheanstalk)
    {
        $this->pheanstalk = $pheanstalk;
        $this->setLogger(new NullLogger);

        $this->jobOptions = array(
            'delay'    => PheanstalkInterface::DEFAULT_DELAY,
            'timeout'  => PheanstalkInterface::DEFAULT_TTR,
            'priority' => PheanstalkInterface::DEFAULT_PRIORITY
        );
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
    public function push($queue, array $payload, array $options = array())
    {
        $options = $this->resolveJobOptions($options);

        return $this->pheanstalk->putInTube(
            $queue,
            json_encode($payload),
            $options['priority'],
            $options['delay'],
            $options['timeout']
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
        $job = $this->pheanstalk->reserveFromTube($queue, $timeout);

        if ($job instanceof Pheanstalk_Job) {
            $job = new BeanstalkdJob($job, $this);

            $job->setQueue($queue);

            return $job;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function delete(JobInterface $job)
    {
        $this->pheanstalk->delete($job->getPheanstalkJob());

        return true;
    }

    /**
     * Bury the job
     *
     * @param  JobInterface $job Job to bury
     * @return boolean Always true
     */
    public function bury(JobInterface $job)
    {
        $this->pheanstalk->bury($job->getPheanstalkJob());

        return true;
    }

    /**
     * {@inheritdoc}
     * @param int|null $priority
     */
    public function release(JobInterface $job, $delay = 0, $priority = PheanstalkInterface::DEFAULT_PRIORITY)
    {
        $this->pheanstalk->release($job->getPheanstalkJob(), $priority, $delay);

        return true;
    }

    /**
     * Return Pheanstalk object
     *
     * @return Pheanstalk
     */
    public function getPheanstalk()
    {
        return $this->pheanstalk;
    }

    /**
     * Set Pheanstalk object
     *
     * @param  PheanstalkInterface $pheanstalk
     * @return BeanstalkdConnector
     */
    public function setPheanstalk(PheanstalkInterface $pheanstalk)
    {
        $this->pheanstalk = $pheanstalk;

        return $this;
    }
}
