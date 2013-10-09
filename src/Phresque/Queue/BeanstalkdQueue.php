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

use Pheanstalk_Job;
use Pheanstalk_Pheanstalk as Pheanstalk;
use Phresque\Job\BeanstalkdJob;

/**
 * Beanstalkd driver
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
class BeanstalkdQueue extends AbstractQueue
{
    public function __construct($queue, $connector = null)
    {
        // In case of array get host and port, otherwise it is just the host
        if(is_array($connector)) {
            $host = $connector['host'];
            $port = @$connector['port'] ?: Pheanstalk::DEFAULT_PORT;
            $this->connect($host, $port);
        } else {
            $this->connect($connector);
        }

        $this->queue = $queue;
    }

    /**
     * Connect to queue
     *
     * @param  string $host Hostname
     * @param  string $port Port number
     * @return null
     */
    public function connect($host, $port = Pheanstalk::DEFAULT_PORT)
    {
        // Is it an instance of Pheanstalk or host and port number?
        if ($host instanceof Pheanstalk) {
            $this->connector = $host;
        } else {
            $this->connector = new Pheanstalk($host, $port);
        }
    }

    public function isAvailable()
    {
        return ($this->connector instanceof Pheanstalk) ? $this->connector->getConnection()->isServiceListening() : false;
    }

    public function push(
        $job,
        $data = null,
        $delay = Pheanstalk::DEFAULT_DELAY,
        $ttr = Pheanstalk::DEFAULT_TTR,
        $priority = Pheanstalk::DEFAULT_PRIORITY
    ) {
        $payload = $this->createPayload($job, $data);

        return $this->connector->putInTube($this->queue, $payload, $priority, $delay, $ttr);
    }

    public function delayed($delay, $job, $data = null)
    {
        return $this->push($job, $data, $delay);
    }

    public function pop($timeout = 0)
    {
        $job = $this->connector->reserveFromTube($this->queue, $timeout);

        if ($job instanceof Pheanstalk_Job) {
            return new BeanstalkdJob($this->connector, $job);
        }
    }
}
