<?php

/*
 * This file is part of the Indigo Queue package.
 *
 * (c) IndigoPHP Development Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Indigo\Queue\Job;

use Indigo\Queue\Connector\DirectConnector;

/**
 * Direct Job
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
class DirectJob extends AbstractJob
{
    public function __construct(array $payload, DirectConnector $connector)
    {
        $this->setPayload($payload);
        $this->connector = $connector;
    }

    /**
     * {@inheritdoc}
     */
    public function attempts()
    {
        return 1;
    }
}
