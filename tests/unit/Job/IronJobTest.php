<?php

namespace Indigo\Queue\Test\Job;

use Indigo\Queue\Job\IronJob;
use Indigo\Queue\Connector\IronConnector;
use IronMQ;

/**
 * Tests for Iron Job
 *
 * @author  Márk Sági-Kazár <mark.sagikazar@gmail.com>
 *
 * @coversDefaultClass  Indigo\Queue\Job\IronJob
 */
class IronJobTest extends JobTest
{
    public function setUp()
    {
        if (isset($GLOBALS['iron_token']) and isset($GLOBALS['iron_project_id'])) {
            $config = array(
                'token'      => $GLOBALS['iron_token'],
                'project_id' => $GLOBALS['iron_project_id'],
            );
        } elseif (getenv('IRON_TOKEN') and getenv('IRON_PROJECT_ID')) {
            $config = array(
                'token'      => getenv('IRON_TOKEN'),
                'project_id' => getenv('IRON_PROJECT_ID'),
            );
        } else {
            $this->markTestSkipped(
                'IronMQ credentials are not available.'
            );

            return;
        }

        $config['protocol'] = 'http';
        $config['port'] = 80;

        $iron = new IronMQ($config);

        $iron->ssl_verifypeer = false;

        $this->connector = new IronConnector($iron);
    }

    /**
     * @covers       ::attempts
     * @covers       ::getIronJob
     * @dataProvider payloadProvider
     * @group        Queue
     */
    public function testJob($payload, $return)
    {
        $queue = 'test_' . uniqid();
        $this->connector->push($queue, $payload);

        $job = $this->connector->pop($queue);

        if ($job instanceof IronJob) {
            $this->assertTrue(is_int($job->attempts()));
            $this->assertInstanceOf(
                'stdClass',
                $job->getIronJob()
            );
        } else {
            $this->assertNull($job);
        }

        return $job;
    }
}
