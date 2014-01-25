<?php

namespace Indigo\Queue\Job;

use Indigo\Queue\Connector\DirectConnector;

class DirectJobTest extends JobTest
{
    public function setUp()
    {
        $this->connector = new DirectConnector;

        $this->queue = uniqid();
    }

    /**
     * @dataProvider payloadProvider
     */
    public function testJob($payload, $return)
    {
        $this->connector->push($this->queue, $payload);

        $job = $this->connector->pop($this->queue);

        if ($job instanceof DirectJob) {
            $this->assertEquals(1, $job->attempts());

            $payload = $job->getPayload();

            $this->assertTrue($job->delete());
        } else {
            $this->assertNull($job);
        }

        return $job;
    }
}