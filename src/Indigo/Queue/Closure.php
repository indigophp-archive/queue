<?php

/*
 * This file is part of the Indigo Queue package.
 *
 * (c) IndigoPHP Development Team
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
     * @var array
     */
    public $config = array('delete' => true);

    /**
     * Execute the Closure job
     *
     * @param JobInterface $job
     * @param array        $data
     */
    public function execute(JobInterface $job, array $data)
    {
        $payload = $job->getPayload();
        $closure = unserialize($payload['closure']);
        return $closure($job, $data);
    }

}
