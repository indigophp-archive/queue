<?php

namespace Indigo\Queue;

use Jeremeamia\SuperClosure\SerializableClosure;
use Codeception\TestCase\Test;

/**
 * Tests for Queue
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 *
 * @coversDefaultClass Indigo\Queue\Queue
 */
class QueueTest extends Test
{
    /**
     * Connector mock
     *
     * @var ConnectorInterface
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
        $this->connector = \Mockery::mock('Indigo\\Queue\\Connector\\ConnectorInterface');

        $this->connecotr->shouldReceive('push')
            ->andReturn(null);

        $this->connecotr->shouldReceive('delayed')
            ->andReturn(null);

        $this->queue = new Queue('test', $this->connector);
    }

    /**
     * @covers ::__construct
     * @group  Queue
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
     */
    public function testConnector()
    {
        $this->assertSame($this->queue, $this->queue->setConnector($this->connector));
        $this->assertSame($this->connector, $this->queue->getConnector());
    }

    /**
     * @covers ::push
     * @group  Queue
     */
    public function testPush()
    {
        $job = \Mockery::mock('Indigo\\Queue\\Job');

        $this->assertNull($this->queue->push($job));
    }

    /**
     * @covers ::delayed
     * @group  Queue
     */
    public function testDelayed()
    {
        $job = \Mockery::mock('Indigo\\Queue\\Job');

        $this->assertNull($this->queue->delayed(0, $job));
    }
}
