<?php

namespace Indigo\Queue\Connector;

class DirectConnectorTest extends ConnectorTest
{
    protected $job;

    public function setUp()
    {
        $this->connector = new DirectConnector;
        $this->job = \Mockery::mock(
            'Indigo\\Queue\\Job\\JobInterface',
            function ($mock) {
                $mock->shouldReceive('getPayload')
                    ->andReturn(array(
                        'job' => 'Job',
                        'data' => array(),
                    ));
            }
        );
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
        $this->assertTrue($this->connector->push('test', $this->job->getPayload()));
    }

    public function testDelayed()
    {
        $this->assertTrue($this->connector->delayed('true', 0.5, $this->job->getPayload()));
    }

    public function testDelete()
    {
        $this->assertTrue($this->connector->delete($this->job));
    }

    public function testRelease()
    {
        $this->assertTrue($this->connector->release($this->job));
    }
}
