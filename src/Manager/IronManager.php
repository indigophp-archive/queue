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

use Indigo\Queue\Adapter\IronAdapter;

/**
 * Iron Manager
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
class IronManager extends AbstractManager
{
    /**
     * Iron job
     *
     * @var stdClass
     */
    protected $ironJob;

    /**
     * Creates a new IronManager
     *
     * @param string        $queue
     * @param stdClass      $job
     * @param IronAdapter $adapter
     */
    public function __construct($queue, \stdClass $job, IronAdapter $adapter)
    {
        $this->ironJob   = $job;
        $this->payload = json_decode($job->body, true);

        parent::__construct($queue, $adapter);
    }

    /**
     * {@inheritdoc}
     */
    public function attempts()
    {
        return $this->ironJob->reserved_count;
    }

    /**
     * Returns the Iron Job
     *
     * @return stdClass
     */
    public function getIronJob()
    {
        return $this->ironJob;
    }
}
