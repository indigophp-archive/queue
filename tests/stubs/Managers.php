<?php

use Indigo\Queue\Manager\AbstractManager;

class DummyManager extends AbstractManager
{
    /**
     * {@inheritdoc}
     */
    public function attempts()
    {
        return 1;
    }
}
