<?php

namespace Indigo\Queue\Test\Connector;

use Indigo\Queue\Connector\IronConnector;
use Indigo\Queue\Job\IronJob;
use IronMQ;

/**
 * Tests for Iron Connector
 *
 * @author  Márk Sági-Kazár <mark.sagikazar@gmail.com>
 *
 * @coversDefaultClass  Indigo\Queue\Connector\IronConnector
 */
class IronConnectorTest extends ConnectorTest
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
     * @covers ::getIron
     * @covers ::setIron
     * @group  Queue
     */
    public function testIron()
    {
        $iron = $this->connector->getIron();

        $this->assertInstanceOf('IronMQ', $iron);

        $this->assertEquals(
            $this->connector,
            $this->connector->setIron($iron)
        );
    }

    /**
     * @covers       ::push
     * @dataProvider payloadProvider
     * @group        Queue
     */
    public function testPush($payload)
    {
        $push = $this->connector->push('test', $payload);
        $this->assertInstanceOf('stdClass', $push);
    }

    /**
     * @covers       ::delayed
     * @dataProvider payloadProvider
     * @group        Queue
     */
    public function testDelayed($payload)
    {
        $delayed = $this->connector->delayed('test', 100, $payload);
        $this->assertInstanceOf('stdClass', $delayed);
    }

    /**
     * @covers       ::pop
     * @covers       ::delete
     * @dataProvider payloadProvider
     * @group        Queue
     */
    public function testPop($payload)
    {
        $queue = 'test_pop_' . uniqid();
        $this->connector->push($queue, $payload);

        if ($job = $this->connector->pop($queue)) {
            $this->assertInstanceOf(
                'Indigo\\Queue\\Job\\IronJob',
                $job
            );

            $this->assertEquals($payload, $job->getPayload());
            $this->assertTrue($this->connector->delete($job));
        } else {
            $this->assertNull($job);
        }

        $this->assertNull($this->connector->pop('null'));
    }

    /**
     * @covers       ::pop
     * @covers       ::release
     * @dataProvider payloadProvider
     * @group        Queue
     */
    public function testRelease($payload)
    {
        $queue = 'test_release_' . uniqid();
        $this->connector->push($queue, $payload);

        if ($job = $this->connector->pop($queue)) {
            $this->assertInstanceOf(
                'Indigo\\Queue\\Job\\IronJob',
                $job
            );

            $this->assertTrue($this->connector->release($job));
        } else {
            $this->assertNull($job);
        }
    }
}