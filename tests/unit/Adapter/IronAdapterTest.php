<?php

/*
 * This file is part of the Indigo Queue package.
 *
 * (c) Indigo Development Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Test\Unit;

use Indigo\Queue\Adapter\IronAdapter;

/**
 * Tests for IronAdapter
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 *
 * @coversDefaultClass Indigo\Queue\Adapter\IronAdapter
 * @group              Queue
 * @group              Adapter
 * @group              Iron
 */
class IronAdapterTest extends AbstractAdapterTest
{
    public function _before()
    {
        $iron = \Mockery::mock('IronMQ');

        $this->adapter = new IronAdapter($iron);
    }

    /**
     * @covers ::__construct
     */
    public function testConstruct()
    {
        $iron = \Mockery::mock('IronMQ');

        $adapter = new IronAdapter($iron);

        $this->assertSame($iron, $adapter->getIron());
    }

    /**
     * @covers ::isConnected
     */
    public function testConnection()
    {
        $this->assertTrue($this->adapter->isConnected());
    }

    /**
     * @covers ::getIron
     * @covers ::setIron
     */
    public function testIron()
    {
        $iron = \Mockery::mock('IronMQ');

        $this->assertSame($this->adapter, $this->adapter->setIron($iron));
        $this->assertSame($iron, $this->adapter->getIron());
    }

    /**
     * @covers                   ::pop
     * @covers                   Indigo\Queue\Exception\QueueEmptyException
     * @expectedException        Indigo\Queue\Exception\QueueEmptyException
     * @expectedExceptionMessage Queue test is empty.
     */
    public function testEmptyPop()
    {
        $iron = $this->adapter->getIron();

        $iron->shouldReceive('getMessage')
            ->andReturn(null);

        $this->adapter->pop('test');
    }

    /**
     * @covers ::count
     */
    public function testCount()
    {
        $stat = new \stdClass;
        $stat->size = 1;

        $iron = $this->adapter->getIron();

        $iron->shouldReceive('getQueue')
            ->andReturn($stat);

        $this->assertEquals(1, $this->adapter->count('test'));
    }
}
