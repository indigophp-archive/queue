<?php

namespace Phresque\Queue;

use Pheanstalk_Job;
use Pheanstalk_Pheanstalk as Pheanstalk;
use Phresque\Connector\BeanstalkdConnector;

class BeanstalkdQueue extends AbstractQueue
{
    public function __construct($queue, $connection)
    {
        $this->connector = new BeanstalkdConnector($connection);
        $this->queue = $queue;
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
        $job = $this->connector->reserveFromTube($queue ?: $this->queue, $timeout);

        if ($job instanceof Pheanstalk_Job)
        {
            return new BeanstalkdJob($this->connector, $job);
        }
    }
}
