<?php

namespace Indigo\Queue\Connector;

class DirectConnectorTest extends ConnectorTest
{
    protected $job;

    public function setUp()
    {
        $this->connector = new DirectConnector;
        $this->queue = uniqid();
    }

    /**
     * @dataProvider payloadProvider
     */
    public function testPush($payload)
    {
        $job = $this->connector->push($this->queue, $payload);

        $this->assertInstanceOf(
            'Indigo\\Queue\\Job\\DirectJob',
            $job
        );

        $this->assertTrue($this->connector->delete($job));
    }

    /**
     * @dataProvider payloadProvider
     */
    public function testDelayed($payload)
    {
        $job = $this->connector->delayed($this->queue, 0.5, $payload);

        $this->assertInstanceOf(
            'Indigo\\Queue\\Job\\DirectJob',
            $job
        );

        $this->assertTrue($this->connector->release($job));
    }
}
