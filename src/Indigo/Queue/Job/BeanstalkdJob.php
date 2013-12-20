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

use Pheanstalk_Job;
use Pheanstalk_Pheanstalk as Pheanstalk;
use Indigo\Queue\Connector\BeanstalkdConnector;

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
    protected $job;

    public function __construct(Pheanstalk_Job $job, BeanstalkdConnector $connector)
    {
        $this->job = $job;
        $this->connector = $connector;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $payload = $this->getPayload();
        return $this->executeJob($payload);
    }

    /**
     * {@inheritdoc}
     */
    public function delete()
    {
        $this->connector->getPheanstalk()->delete($this->job);
    }

    /**
     * {@inheritdoc}
     */
    public function bury()
    {
        $this->connector->getPheanstalk()->bury($this->job);
    }

    /**
     * {@inheritdoc}
     */
    public function release($delay = 0, $priority = Pheanstalk::DEFAULT_PRIORITY)
    {
        $this->connector->getPheanstalk()->release($this->job, $priority, $delay);
    }

    /**
     * {@inheritdoc}
     */
    public function attempts()
    {
        $stats = $this->connector->getPheanstalk()->statsJob($this->job);

        return (int) $stats->reserves;
    }

    /**
     * {@inheritdoc}
     */
    public function getPayload()
    {
        return json_decode($this->job->getData(), true);
    }

    /**
     * Get job object
     *
     * @return object
     */
    public function getJob()
    {
        return $this->job;
    }
}
