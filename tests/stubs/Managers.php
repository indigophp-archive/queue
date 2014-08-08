<?php

/*
 * This file is part of the Indigo Queue package.
 *
 * (c) Indigo Development Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Indigo\Queue\Manager\AbstractManager;

/**
 * Dummy Manager
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
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
