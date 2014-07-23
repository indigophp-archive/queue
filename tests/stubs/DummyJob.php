<?php

use Indigo\Queue\Job\JobInterface;
use Indigo\Queue\Manager\ManagerInterface;

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
    public function execute(ManagerInterface $manager)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function fail(ManagerInterface $manager, \Exception $e = null)
    {
        return true;
    }
}
