<?php

namespace Indigo\Queue\Job;

use Indigo\Queue\Connector\IronConnector;
use IronMQ;

class IronJobTest extends JobTest
{
    public function setUp()
    {
        if (isset($GLOBALS['iron_token']) and isset($GLOBALS['iron_project_id'])) {
            $config = array(
                'token'      => $GLOBALS['iron_token'],
                'project_id' => $GLOBALS['iron_project_id'],
            );
        } elseif (isset($_ENV['iron_token']) and isset($_ENV['iron_project_id'])) {
            $config = array(
                'token'      => $_ENV['iron_token'],
                'project_id' => $_ENV['iron_project_id'],
            );
        } else {
            $this->markTestSkipped(
                'IronMQ credentials are not available.'
            );

            return;
        }

        $iron = new IronMQ($config);

        $this->connector = new IronConnector($iron);
    }

    /**
     * @dataProvider payloadProvider
     */
    public function testJob($payload, $return)
    {
        $this->connector->push('test', $payload);

        $job = $this->connector->pop('test');

        if ($job instanceof IronJob) {
            $this->assertEquals(1, $job->attempts());
            $this->assertInstanceOf(
                'stdClass',
                $job->getIronJob()
            );

            $this->assertTrue($job->delete());
        } else {
            $this->assertNull($job);
        }

        return $job;
    }
}
