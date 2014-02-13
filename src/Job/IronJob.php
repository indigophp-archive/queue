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

use Indigo\Queue\Connector\IronConnector;
use Psr\Log\NullLogger;
use stdClass;

/**
 * Iron Job
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
class IronJob extends AbstractJob
{
    protected $ironJob;

    public function __construct(stdClass $job, IronConnector $connector)
    {
        $this->ironJob = $job;
        $this->connector = $connector;
        $this->setPayload(json_decode($job->body, true));
        $this->setLogger(new NullLogger);
    }

    /**
     * {@inheritdoc}
     */
    public function attempts()
    {
        return $this->ironJob->reserved_count;
    }

    /**
     * Get Iron Job
     *
     * @return stdClass
     */
    public function getIronJob()
    {
        return $this->ironJob;
    }
}
