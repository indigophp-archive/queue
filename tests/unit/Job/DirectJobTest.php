<?php

namespace Indigo\Queue\Test\Job;

use Indigo\Queue\Job\DirectJob;
use Indigo\Queue\Connector\DirectConnector;

/**
 * Tests for Direct Job
 *
 * @author  Márk Sági-Kazár <mark.sagikazar@gmail.com>
 *
 * @coversDefaultClass  Indigo\Queue\Job\DirectJob
 */
class DirectJobTest extends JobTest
{
    public function setUp()
    {
        $this->connector = new DirectConnector;
    }

    /**
     * @covers       ::attempts
     * @covers       ::getPayload
     * @dataProvider payloadProvider
     * @group        Queue
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