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

use Indigo\Queue\Manager\ManagerInterface;

/**
 * Job interface
 *
 * Generic interface for jobs
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
interface JobInterface
{
    /**
     * This is the execute callback
     *
     * @param ManagerInterface $manager
     *
     * @return mixed
     */
    public function execute(ManagerInterface $manager);

    /**
     * This is the failure callback
     *
     * @param ManagerInterface $manager
     * @param Exception        $e Any exceptions thrown during execution
     *
     * @return mixed
     */
    public function fail(ManagerInterface $manager, \Exception $e = null);
}
