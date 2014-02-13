<?php

namespace Indigo\Queue\Test\Job;

use Indigo\Queue\Job\DirectJob;
use Indigo\Queue\Connector\DirectConnector;

class DirectJobTest extends JobTest
{
    public function setUp()
    {
        $this->connector = new DirectConnector;
    }

    /**
     * @dataProvider payloadProvider
     */
    public function testJob($payload, $return)
    {
        $this->connector->push('test', $payload);

        $job = $this->connector->pop('test');

        if ($job instanceof DirectJob) {
            $this->assertEquals(1, $job->attempts());

            $payload = $job->getPayload();
        } else {
            $this->assertNull($job);
        }

        return $job;
    }
}