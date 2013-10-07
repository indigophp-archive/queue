<?php

namespace Phresque\Queue;

use Pheanstalk_Job;
use Pheanstalk_Pheanstalk as Pheanstalk;
use Phresque\Connector\BeanstalkdConnector;

class BeanstalkdQueue extends AbstractQueue
{
    public function __construct($queue, $connector = null)
    {
        if(is_array($connector)) {
            $host = $connector['host'];
            $port = @$connector['port'] ?: Pheanstalk::DEFAULT_PORT;
            $this->connect($host, $port);
        } else {
            $this->connect($connector);
        }

        $this->queue = $queue;
    }

    public function connect($host, $port = Pheanstalk::DEFAULT_PORT)
    {
        if ($host instanceof Pheanstalk) {
            $this->connector = $host;
        }
        else
        {
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

        return $this->connector->putInTube($this->queue, $payload);
    }

    public function delayed($delay, $job, $data = null)
    {
        return $this->push($job, $data, $delay);
    }

    public function pop($timeout = null)
    {
        $job = $this->connector->reserveFromTube($this->queue, $timeout);

        if ($job instanceof Pheanstalk_Job)
        {
            return new BeanstalkdJob($this->connector, $job);
        }
    }
}
