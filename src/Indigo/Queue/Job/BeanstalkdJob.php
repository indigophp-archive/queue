<?php

/*
 * This file is part of the Indigo Queue package.
 *
 * (c) IndigoPHP Development Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Indigo\Queue\Job;

use Indigo\Queue\Connector\BeanstalkdConnector;
use Pheanstalk_Job;
use Pheanstalk_Pheanstalk as Pheanstalk;
use Pheanstalk_PheanstalkInterface as PheanstalkInterface;

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
        $this->connector     = $connector;
    }

    /**
     * {@inheritdoc}
     */
    public function delete()
    {
        $this->connector->getPheanstalk()->delete($this->pheanstalkJob);
    }

    /**
     * {@inheritdoc}
     */
    public function bury()
    {
        $this->connector->getPheanstalk()->bury($this->pheanstalkJob);
    }

    /**
     * {@inheritdoc}
     */
    public function release($delay = 0, $priority = PheanstalkInterface::DEFAULT_PRIORITY)
    {
        $this->connector->getPheanstalk()->release($this->pheanstalkJob, $priority, $delay);
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

    /**
     * {@inheritdoc}
     * Get/Regenerate payload
     *
     * @param boolean $regenerate
     */
    public function getPayload($regenerate = false)
    {
        if ($regenerate === true or empty($this->payload)) {
            return $this->payload = json_decode($this->pheanstalkJob->getData(), true);
        }

        return $this->payload;
    }
}
