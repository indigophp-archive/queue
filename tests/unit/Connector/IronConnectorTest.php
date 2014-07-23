<?php

namespace Test\Unit;

use Indigo\Queue\Connector\IronConnector;

/**
 * Tests for IronConnector
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 *
 * @coversDefaultClass Indigo\Queue\Connector\IronConnector
 * @group              Queue
 * @group              Connector
 * @group              Iron
 */
class IronConnectorTest extends AbstractConnectorTest
{
    public function _before()
    {
        $iron = \Mockery::mock('IronMQ');

        $this->connector = new IronConnector($iron);
    }

    /**
     * @covers ::__construct
     */
    public function testConstruct()
    {
        $iron = \Mockery::mock('IronMQ');

        $connector = new IronConnector($iron);

        $this->assertSame($iron, $connector->getIron());
    }

    /**
     * @covers ::isConnected
     */
    public function testConnection()
    {
        $this->assertTrue($this->connector->isConnected());
    }

    /**
     * @covers ::getIron
     * @covers ::setIron
     */
    public function testIron()
    {
        $iron = \Mockery::mock('IronMQ');

        $this->assertSame($this->connector, $this->connector->setIron($iron));
        $this->assertSame($iron, $this->connector->getIron());
    }

    /**
     * @covers                   ::pop
     * @covers                   Indigo\Queue\Exception\QueueEmptyException
     * @expectedException        Indigo\Queue\Exception\QueueEmptyException
     * @expectedExceptionMessage Queue test is empty.
     */
    public function testEmptyPop()
    {
        $iron = $this->connector->getIron();

        $iron->shouldReceive('getMessage')
            ->andReturn(null);

        $this->connector->pop('test');
    }

    /**
     * @covers ::count
     */
    public function testCount()
    {
        $stat = new \stdClass;
        $stat->size = 1;

        $iron = $this->connector->getIron();

        $iron->shouldReceive('getQueue')
            ->andReturn($stat);

        $this->assertEquals(1, $this->connector->count('test'));
    }
}
