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

use Indigo\Queue\Connector\IronConnector;
use Psr\Log\NullLogger;

/**
 * Iron Job
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
class BeanstalkdJob extends AbstractJob
{
    protected $ironJob;

    public function __construct(\stdClass $job, IronConnector $connector)
    {
        $this->ironJob   = $job;
        $this->connector = $connector;
        $this->setLogger(new NullLogger);
    }

    /**
     * {@inheritdoc}
     */
    public function delete()
    {
        $this->connector->getIron()->deleteMessage($this->pheanstalkJob);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function bury()
    {
        $this->connector->getPheanstalk()->bury($this->pheanstalkJob);

        return true;
    }

    /**
     * {@inheritdoc}
     * @param int|null $priority
     */
    public function release($delay = 0, $priority = PheanstalkInterface::DEFAULT_PRIORITY)
    {
        $this->connector->getPheanstalk()->release($this->pheanstalkJob, $priority, $delay);

        return true;
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
            return $this->payload = json_decode($this->ironJob->body, true);
        }

        return parent::getPayload();
    }
}
