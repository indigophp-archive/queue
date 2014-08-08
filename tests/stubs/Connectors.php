<?php

/*
 * This file is part of the Indigo Queue package.
 *
 * (c) Indigo Development Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Indigo\Queue\Connector\AbstractConnector;
use Indigo\Queue\Job;
use Indigo\Queue\Manager\ManagerInterface;

class DummyConnector extends AbstractConnector
{
    /**
     * {@inheritdoc}
     */
    protected $managerClass = 'Fake\\Class';

    /**
     * {@inheritdoc}
     */
    public function isConnected()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function push($queue, Job $job)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function delayed($queue, $delay, Job $job)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function pop($queue, $timeout = 0)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function count($queue)
    {
        return 1;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(ManagerInterface $manager)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function clear($queue)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function release(ManagerInterface $manager, $delay = 0)
    {
        return true;
    }
}