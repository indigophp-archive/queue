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

    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    public function execute()
    {
        $this->executeJob($this->payload);
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
