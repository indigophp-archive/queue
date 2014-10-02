<?php

/*
 * This file is part of the Indigo Queue package.
 *
 * (c) Indigo Development Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Indigo\Queue\Job\JobInterface;
use Indigo\Queue\Job\AbstractJob;
use Indigo\Queue\Manager;

/**
 * Dummy Job
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
class DummyJob implements JobInterface
{
    public $config = array(
        'retry'  => 2,
        'delete' => true,
        'bury'   => true,
    );

    /**
     * {@inheritdoc}
     */
    public static function factory(Manager $manager)
    {
        return new static;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(Manager $manager)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function fail(Manager $manager, \Exception $e = null)
    {
        return true;
    }
}

/**
 * Dummy Job
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
class AdvancedJob extends AbstractJob
{
    /**
     * {@inheritdoc}
     */
    public function execute(Manager $manager)
    {
        return true;
    }
}
