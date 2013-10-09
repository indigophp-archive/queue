<?php
/*
 * This file is part of the Phresque package.
 *
 * (c) Márk Sági-Kazár <mark.sagikazar@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phresque\Job;

/**
 * Direct Job
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
class DirectJob extends AbstractJob
{
    /**
     * Payload data
     *
     * @var array
     */
    protected $payload;

    public function __construct($job, $data = null)
    {
        $this->payload = array('job' => $job, 'data' => $data);
    }

    public function execute()
    {
        $this->runJob($this->payload);
    }

    public function delete() { }

    public function bury() { }

    public function release($delay = 0) { }

    public function attempts()
    {
        return 1;
    }

    public function getPayload()
    {
        return $this->payload;
    }
}
