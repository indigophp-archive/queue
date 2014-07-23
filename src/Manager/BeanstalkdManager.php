<?php

/*
 * This file is part of the Indigo Queue package.
 *
 * (c) Indigo Development Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Indigo\Queue\Manager;

use Indigo\Queue\Connector\BeanstalkdConnector;
use Pheanstalk_Job;
use Pheanstalk_Pheanstalk as Pheanstalk;

/**
 * Beanstalkd Manager
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
class BeanstalkdManager extends AbstractManager
{
    /**
     * Pheanstalk Job
     *
     * @var Pheanstalk_Job
     */
    protected $pheanstalkJob;

    /**
     * Creates a new BeanstalkdManager
     *
     * @param string              $queue
     * @param Pheanstalk_Job      $job
     * @param BeanstalkdConnector $connector
     */
    public function __construct($queue, Pheanstalk_Job $job, BeanstalkdConnector $connector)
    {
        $this->pheanstalkJob = $job;
        $this->payload = json_decode($job->getData(), true);

        parent::__construct($queue, $connector);
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
     * Returns the Pheanstalk Job
     *
     * @return Pheanstalk_Job
     */
    public function getPheanstalkJob()
    {
        return $this->pheanstalkJob;
    }
}
