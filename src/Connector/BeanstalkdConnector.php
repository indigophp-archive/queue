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
use Pheanstalk_Job;
use Pheanstalk_Pheanstalk as Pheanstalk;
use Pheanstalk_PheanstalkInterface as PheanstalkInterface;
use Pheanstalk_Exception_ServerException as ServerException;
use Psr\Log\NullLogger;

/**
 * Beanstalkd connector
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
class BeanstalkdConnector extends AbstractConnector
{
    /**
     * {@inheritdoc}
     */
    protected $options = [
        'delay'    => PheanstalkInterface::DEFAULT_DELAY,
        'timeout'  => PheanstalkInterface::DEFAULT_TTR,
        'priority' => PheanstalkInterface::DEFAULT_PRIORITY
    ];

    /**
     * {@inheritdoc}
     */
    protected $jobClass = 'Indigo\\Queue\\Job\\BeanstalkdJob';

    /**
     * Pheanstalk object
     *
     * @var Pheanstalk
     */
    protected $pheanstalk = null;

    /**
     * Creates a new BeanstalkdConnector
     *
     * @param PheanstalkInterface $pheanstalk
     */
    public function __construct(PheanstalkInterface $pheanstalk)
    {
        $this->pheanstalk = $pheanstalk;

        parent::__construct();
    }

    /**
     * Returns a Pheanstalk object
     *
     * @return Pheanstalk
     */
    public function getPheanstalk()
    {
        return $this->pheanstalk;
    }

    /**
     * Sets a Pheanstalk object
     *
     * @param PheanstalkInterface $pheanstalk
     *
     * @return BeanstalkdConnector
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
    public function push($queue, array $payload, array $options = [])
    {
        $options = $this->options + $options;

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
    public function delayed($queue, $delay, array $payload, array $options = [])
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
        $class = $this->jobClass;

        if ($job instanceof Pheanstalk_Job) {
            $job = new $class($job, $this);
            $job->setQueue($queue);

            return $job;
        }
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
    public function delete(JobInterface $job)
    {
        $this->pheanstalk->delete($job->getPheanstalkJob());

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function clear($queue)
    {
        $this->doClear('ready');
        $this->doClear('buried');
        $this->doClear('delayed');
    }

    /**
     * Clears a specific state
     *
     * @param string $state
     *
     * @return boolean
     */
    protected function doClear($state)
    {
        try {
            while ($item = $this->pheanstalk->{'peek'.$state}($queue)) {
                $this->pheanstalk->delete($item);
            }
        } catch (ServerException $e) {
        }

        return true;
    }

    /**
     * Bury the job
     *
     * @param JobInterface $job Job to bury
     *
     * @return boolean Always true
     */
    public function bury(JobInterface $job)
    {
        $this->pheanstalk->bury($job->getPheanstalkJob());

        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @param integer|null $priority
     */
    public function release(JobInterface $job, $delay = 0, $priority = PheanstalkInterface::DEFAULT_PRIORITY)
    {
        $this->pheanstalk->release($job->getPheanstalkJob(), $priority, $delay);

        return true;
    }
}
