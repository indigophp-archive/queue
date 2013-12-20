<?php
/*
 * This file is part of the Indigo Queue package.
 *
 * (c) IndigoPHP Development Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Indigo\Queue\Connector;

use Indigo\Queue\Job\JobInterface;
use Indigo\Queue\Job\DirectJob;

/**
 * Direct driver for running jobs immediately
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
class DirectConnector extends AbstractConnector
{
    public function isConnected()
    {
        return true;
    }

    public function push(array $payload = array(), array $options = array())
    {
        $job = new DirectJob($payload);
        $job->setLogger($this->logger);
        return $job->execute();
    }

    public function delayed($delay, array $payload = array(), array $options = array())
    {
        sleep($delay);
        return $this->push($payload, $options);
    }

    public function pop($queue, $timeout = 0) { }
}
