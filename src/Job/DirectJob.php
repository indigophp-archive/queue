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

use Indigo\Queue\Connector\DirectConnector;
use Psr\Log\NullLogger;

/**
 * Direct Job
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
class DirectJob extends AbstractJob
{
    /**
     * @codeCoverageIgnore
     */
    public function __construct(array $payload, DirectConnector $connector)
    {
        $this->connector = $connector;
        $this->logger    = new NullLogger;

        $this->setPayload($payload);
    }

    /**
     * {@inheritdoc}
     */
    public function attempts()
    {
        return 1;
    }
}
