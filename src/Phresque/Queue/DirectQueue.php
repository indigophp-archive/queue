<?php
/*
 * This file is part of the Phresque package.
 *
 * (c) Márk Sági-Kazár <mark.sagikazar@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phresque\Queue;

use Phresque\Job\JobInterface;

/**
 * Direct driver for running jobs immediately
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
class DirectQueue extends AbstractQueue
{
    public function connect($connector = null) { }

    public function isAvailable()
    {
        return true;
    }

    public function push($job, $data = null)
    {
        $job = new DirectJob($job, $data);
        return $job->execute();
    }

    public function delayed($delay, $job, $data = null)
    {
        sleep($delay);
        return $this->push($job, $data);
    }

    public function pop($timeout = 0) { }
}
