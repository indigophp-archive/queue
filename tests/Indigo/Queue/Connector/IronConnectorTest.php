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

        $this->assertNull($this->connector->pop('null'));
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
