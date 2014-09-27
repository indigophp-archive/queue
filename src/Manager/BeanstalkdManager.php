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

use Indigo\Queue\Adapter\BeanstalkdAdapter;
use Pheanstalk\Job as PheanstalkJob;
use Pheanstalk\Pheanstalk;

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
     * @var PheanstalkJob
     */
    protected $pheanstalkJob;

    /**
     * Creates a new BeanstalkdManager
     *
     * @param string              $queue
     * @param PheanstalkJob      $job
     * @param BeanstalkdAdapter $adapter
     */
    public function __construct($queue, PheanstalkJob $job, BeanstalkdAdapter $adapter)
    {
        $this->pheanstalkJob = $job;
        $this->payload = json_decode($job->getData(), true);

        parent::__construct($queue, $adapter);
    }

    /**
     * {@inheritdoc}
     */
    public function attempts()
    {
        $stats = $this->adapter->getPheanstalk()->statsJob($this->pheanstalkJob);

        return (int) $stats->reserves;
    }

    /**
     * Returns the Pheanstalk Job
     *
     * @return PheanstalkJob
     */
    public function getPheanstalkJob()
    {
        return $this->pheanstalkJob;
    }
}
