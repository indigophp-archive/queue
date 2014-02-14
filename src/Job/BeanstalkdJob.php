<?php

/*
 * This file is part of the Indigo Queue package.
 *
 * (c) Indigo Development Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Indigo\Queue\Job;

use Indigo\Queue\Connector\BeanstalkdConnector;
use Pheanstalk_Job;
use Pheanstalk_Pheanstalk as Pheanstalk;
use Psr\Log\NullLogger;

/**
 * Beanstalkd Job
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
class BeanstalkdJob extends AbstractJob
{
    /**
     * Pheanstalk Job
     *
     * @var Pheanstalk_Job
     */
    protected $pheanstalkJob;

    public function __construct(Pheanstalk_Job $job, BeanstalkdConnector $connector)
    {
        $this->pheanstalkJob = $job;
        $this->connector = $connector;
        $this->setPayload(json_decode($job->getData(), true));
        $this->setLogger(new NullLogger);
    }

    /**
     * {@inheritdoc}
     */
    public function attempts()
    {
        $stats = $this->connector->getPheanstalk()->statsJob($this->pheanstalkJob);

        return (int) $stats->reserves;
    }

    /**
     * Get Pheanstalk Job
     *
     * @return Pheanstalk_Job
     */
    public function getPheanstalkJob()
    {
        return $this->pheanstalkJob;
    }
}
