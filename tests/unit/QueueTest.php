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

use Jeremeamia\SuperClosure\SerializableClosure;
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
     * Connector mock
     *
     * @var Indigo\Queue\Connector
     */
    protected $connector;

    /**
     * Queue object
     *
     * @var Queue
     */
    protected $queue;

    public function _before()
    {
        $this->connector = \Mockery::mock('Indigo\\Queue\\Connector');

        $this->queue = new Queue('test', $this->connector);
    }

    /**
     * @covers ::__construct
     * @group  Queue
     * @group  Main
     */
    public function testConstruct()
    {
        $queue = new Queue('test', $this->connector);

        $this->assertSame($this->connector, $queue->getConnector());
        $this->assertEquals('test', $queue->getQueue());
    }

    /**
     * @covers ::getQueue
     * @covers ::__toString
     * @group  Queue
     * @group  Main
     */
    public function testQueue()
    {
        $this->assertEquals('test', $this->queue->getQueue());
        $this->assertEquals('test', (string) $this->queue);
    }

    /**
     * @covers ::getConnector
     * @covers ::setConnector
     * @group  Queue
     * @group  Main
     */
    public function testConnector()
    {
        $this->assertSame($this->queue, $this->queue->setConnector($this->connector));
        $this->assertSame($this->connector, $this->queue->getConnector());
    }

    /**
     * @covers ::push
     * @group  Queue
     * @group  Main
     */
    public function testPush()
    {
        $this->connector->shouldReceive('push')
            ->andReturn(null);

        $job = \Mockery::mock('Indigo\\Queue\\Job');

        $this->assertNull($this->queue->push($job));
    }

    /**
     * @covers ::delayed
     * @group  Queue
     * @group  Main
     */
    public function testDelayed()
    {
        $this->connector->shouldReceive('delayed')
            ->andReturn(null);

        $job = \Mockery::mock('Indigo\\Queue\\Job');

        $this->assertNull($this->queue->delayed(0, $job));
    }
}
