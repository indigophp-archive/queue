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
    /**
     * IronMQ mock
     *
     * @var IronMQ
     */
    protected $iron;

    public function _before()
    {
        $this->iron = \Mockery::mock('IronMQ');

        $this->adapter = new IronAdapter($this->iron);
    }

    /**
     * @covers ::__construct
     */
    public function testConstruct()
    {
        $adapter = new IronAdapter($this->iron);

        $this->assertSame($this->iron, $adapter->getIron());
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
     * @covers ::push
     */
    public function testPush()
    {
        $this->iron
            ->shouldReceive('postMessage')
            ->andReturn(true);

        parent::testPush();
    }

    /**
     * @covers ::pop
     */
    public function testPop()
    {
        $message = new \stdClass;
        $message->id = 1;
        $message->body = json_encode([]);
        $message->reserved_count = 1;

        $this->iron
            ->shouldReceive('getMessage')
            ->andReturn($message);

        parent::testPop();
    }

    /**
     * @covers                   ::pop
     * @covers                   Indigo\Queue\Exception\QueueEmptyException
     * @expectedException        Indigo\Queue\Exception\QueueEmptyException
     * @expectedExceptionMessage Queue test is empty
     */
    public function testEmptyPop()
    {
        $this->iron->shouldReceive('getMessage');

        parent::testEmptyPop();
    }

    /**
     * @covers ::count
     */
    public function testCount()
    {
        $stat = new \stdClass;
        $stat->size = 1;

        $this->iron->shouldReceive('getQueue')
            ->andReturn($stat);

        parent::testCount();
    }

    /**
     * @covers ::delete
     */
    public function testDelete()
    {
        $this->iron->shouldReceive('deleteMessage');

        parent::testDelete();
    }

    /**
     * @covers ::clear
     */
    public function testClear()
    {
        $this->iron->shouldReceive('clearQueue');

        parent::testClear();
    }

    /**
     * @covers ::release
     */
    public function testRelease()
    {
        $this->iron->shouldReceive('releaseMessage');

        parent::testRelease();
    }
}
