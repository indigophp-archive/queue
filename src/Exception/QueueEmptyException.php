<?php

/*
 * This file is part of the Indigo Queue package.
 *
 * (c) Indigo Development Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Indigo\Queue\Exception;

/**
 * Queue Empty Exception
 *
 * Connectors should throw this exception if no job can be popped
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
class QueueEmptyException extends \UnderflowException
{
    /**
     * Creates a new QueueEmptyException
     *
     * @param string $queue Queue name
     */
    public function __construct($queue)
    {
        parent::__construct('Queue ' . $queue . ' is empty.');
    }
}
