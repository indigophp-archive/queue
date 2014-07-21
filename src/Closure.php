<?php

/*
 * This file is part of the Indigo Queue package.
 *
 * (c) Indigo Development Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Indigo\Queue;

use Indigo\Queue\Job\JobInterface;

/**
 * Closure job
 *
 * This job processes closures pushed to queue
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
class Closure
{
    /**
     * Config
     *
     * @var []
     */
    public $config = ['delete' => true];

    /**
     * Creates a new Closure
     *
     * @param JobInterface $job
     * @param []           $data
     */
    public function __construct(JobInterface $job, array $data)
    {
        if (isset($data['config'])) {
            $this->config = array_merge($this->config, $data['config']);
        }
    }

    /**
     * Executes the Closure job
     *
     * @param JobInterface $job
     * @param array        $data
     *
     * @return mixed
     */
    public function execute(JobInterface $job, array $data)
    {
        $payload = $job->getPayload();
        $closure = unserialize($payload['closure']);

        return $closure($job, $data);
    }
}
