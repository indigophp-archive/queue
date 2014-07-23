<?php

/*
 * This file is part of the Indigo Queue package.
 *
 * (c) Indigo Development Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Indigo\Queue\Manager;

use Indigo\Queue\Connector\DirectConnector;

/**
 * Direct Manager
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
class DirectManager extends AbstractManager
{
    /**
     * Creates a new DirectManager
     *
     * @param string          $queue
     * @param []              $payload
     * @param DirectConnector $connector
     */
    public function __construct($queue, array $payload, DirectConnector $connector)
    {
        $this->payload = $payload;

        parent::__construct($queue, $connector);
    }

    /**
     * {@inheritdoc}
     */
    public function attempts()
    {
        return 1;
    }
}
