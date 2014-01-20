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

/**
 * Connector Inteface
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
interface ConnectorInterface
{
    /**
     * Checks whether connection is available
     *
     * @return boolean
     */
    public function isConnected();

    /**
    * Push a new job onto the queue
    *
    * @param  array $payload
    * @param  array $options
    * @return mixed
    */
    public function push(array $payload, array $options = array());

    /**
    * Push a new job onto the queue after a delay
    *
    * @param  int   $delay
    * @param  array $payload
    * @param  array $options
    * @return mixed
    */
    public function delayed($delay, array $payload, array $options = array());

    /**
    * Pop the next job off of the queue
    *
    * @param  string       $queue   Name of the queue
    * @param  integer      $timeout Wait timeout
    * @return JobInterface
    */
    public function pop($queue, $timeout = 0);
}
