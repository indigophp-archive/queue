<?php

namespace Indigo\Queue\Connector;

class DirectConnectorTest extends ConnectorTest
{

    protected $connector = null;

    public function setUp()
    {
        $this->connector = new DirectConnector;
    }

    public function testInstance()
    {
        $this->assertInstanceOf(
            'Indigo\\Queue\\Connector\\DirectConnector',
            new DirectConnector
        );
    }

    public function testPush()
    {
        $payload = array(
            'job' => 'Job',
            'data' => array(),
        );

        $this->assertTrue($this->connector->push($payload));
    }

    public function testDelay()
    {
        $payload = array(
            'job' => 'Job',
            'data' => array(),
        );

        $this->assertTrue($this->connector->delayed(0.5, $payload));
    }
}
