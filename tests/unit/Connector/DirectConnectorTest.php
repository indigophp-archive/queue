<?php

namespace Indigo\Queue\Test\Connector;

use Indigo\Queue\Connector\DirectConnector;

/**
 * Tests for Direct Connector
 *
 * @author  Márk Sági-Kazár <mark.sagikazar@gmail.com>
 *
 * @coversDefaultClass  Indigo\Queue\Connector\DirectConnector
 */
class DirectConnectorTest extends ConnectorTest
{
    protected $job;

    public function setUp()
    {
        $this->connector = new DirectConnector;
    }

    /**
     * @covers       ::push
     * @covers       ::pop
     * @covers       ::delete
     * @dataProvider payloadProvider
     * @group        Queue
     */
    public function testPush($payload)
    {
        $job = $this->connector->push('test', $payload);

        $this->assertInstanceOf(
            'Indigo\\Queue\\Job\\DirectJob',
            $job
        );

        $this->assertTrue($this->connector->delete($job));
    }

    /**
     * @covers       ::push
     * @covers       ::pop
     * @covers       ::delayed
     * @covers       ::release
     * @dataProvider payloadProvider
     * @group        Queue
     */
    public function testDelayed($payload)
    {
        $job = $this->connector->delayed('test', 0.5, $payload);

        $this->assertInstanceOf(
            'Indigo\\Queue\\Job\\DirectJob',
            $job
        );

        $this->assertTrue($this->connector->release($job));
    }
}
