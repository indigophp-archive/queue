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

use Indigo\Queue\Manager;

/**
 * Closure job
 *
 * This job processes closures pushed to queue
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
class ClosureJob implements JobInterface
{
    /**
     * Config
     *
     * @var []
     */
    public $config = ['delete' => true];

    /**
     * {@inheritdoc}
     */
    public function execute(Manager $manager)
    {
        $payload = $manager->getPayload();
        $closure = unserialize($payload['closure']);

        return $closure($manager);
    }

    /**
     * {@inheritdoc}
     */
    public function fail(Manager $manager, \Exception $e = null)
    {
    }
}
