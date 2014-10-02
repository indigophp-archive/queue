<?php

/*
 * This file is part of the Indigo Queue package.
 *
 * (c) Indigo Development Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Indigo\Queue;

use Codeception\TestCase\Test;

/**
 * Tests for Queue
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 *
 * @coversDefaultClass Indigo\Queue\Queue
 * @group              Queue
 * @group              Main
 */
class QueueTest extends Test
{
    /**
     * Adapter mock
     *
     * @var Indigo\Queue\Adapter
     */
    protected $adapter;

    /**
     * Queue object
     *
     * @var Queue
     */
    protected $queue;

    public function _before()
    {
        $this->adapter = \Mockery::mock('Indigo\\Queue\\Adapter');

        $this->queue = new Queue('test', $this->adapter);
    }

    /**
     * @covers ::__construct
     */
    public function testConstruct()
    {
        $queue = new Queue('test', $this->adapter);

        $this->assertSame($this->adapter, $queue->getAdapter());
        $this->assertEquals('test', $queue->getQueue());
    }

    /**
     * @covers ::getQueue
     * @covers ::__toString
     */
    public function testQueue()
    {
        $this->assertEquals('test', $this->queue->getQueue());
        $this->assertEquals('test', (string) $this->queue);
    }

    /**
     * @covers ::getAdapter
     * @covers ::setAdapter
     */
    public function testAdapter()
    {
        $this->assertSame($this->queue, $this->queue->setAdapter($this->adapter));
        $this->assertSame($this->adapter, $this->queue->getAdapter());
    }

    /**
     * @covers ::push
     */
    public function testPush()
    {
        $this->adapter->shouldReceive('push')
            ->andReturn(null);

        $message = \Mockery::mock('Indigo\\Queue\\Message');
        $message->shouldReceive('setQueue');

        $this->assertNull($this->queue->push($message));
    }
}
