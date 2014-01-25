<?php

namespace Indigo\Queue\Connector;

use Indigo\Queue\Job\IronJob;
use IronMQ;

class IronConnectorTest extends ConnectorTest
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

    public function testConnection()
    {
        $this->assertTrue($this->connector->isConnected());
    }

    public function testIron()
    {
        $iron = $this->connector->getIron();

        $this->assertInstanceOf('IronMQ', $iron);

        $this->assertInstanceOf(
            'Indigo\\Queue\\Connector\\IronConnector',
            $this->connector->setIron($iron)
        );
    }

    /**
     * @dataProvider payloadProvider
     */
    public function testPush($payload)
    {
        $push = $this->connector->push('test', $payload);
        $this->assertInstanceOf('stdClass', $push);
    }

    /**
     * @dataProvider payloadProvider
     */
    public function testDelayed($payload)
    {
        $delayed = $this->connector->delayed('test', 100, $payload);
        $this->assertInstanceOf('stdClass', $delayed);
    }

    /**
     * @dataProvider payloadProvider
     */
    public function testPop($payload)
    {
        $this->connector->push('test_pop', $payload);

        if ($job = $this->connector->pop('test_pop')) {
            $this->assertInstanceOf(
                'Indigo\\Queue\\Job\\IronJob',
                $job
            );

            $this->assertEquals($payload, $job->getPayload());
            $this->assertTrue($this->connector->delete($job));
        } else {
            $this->assertNull($job);
        }
    }

    /**
     * @dataProvider payloadProvider
     */
    public function testRelease($payload)
    {
        $this->connector->push('test_release', $payload);

        if ($job = $this->connector->pop('test_release')) {
            $this->assertInstanceOf(
                'Indigo\\Queue\\Job\\IronJob',
                $job
            );

            $this->assertTrue($this->connector->release($job));
        } else {
            $this->assertNull($job);
        }

        $job = $this->connector->pop('test_release');
        $this->connector->delete($job);
    }
}
