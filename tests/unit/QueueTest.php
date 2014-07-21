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
    protected $queue;

    public function _before()
    {
        $connector = \Mockery::mock('Indigo\\Queue\\Connector\\ConnectorInterface');

        $connector->shouldReceive('push')
            ->andReturn(null);

        $connector->shouldReceive('delayed')
            ->andReturn(null);

        $this->queue = new Queue('test', $connector);
    }

    public function jobProvider()
    {
        return array(
            array(
                'Job@runThis',
                array(),
            ),
            array(
                function () {
                    return true;
                },
                array(),
            ),
        );
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
        $connector = $this->queue->getConnector();

        $this->assertSame($this->queue, $this->queue->setConnector($connector));

        $this->assertSame($connector, $this->queue->getConnector());
    }

    /**
     * @covers       ::push
     * @covers       ::createPayload
     * @dataProvider jobProvider
     * @group        Queue
     */
    public function testPush($job, $data)
    {
        $payload = $this->queue->push($job, $data);

        if ($job instanceof \Closure) {
            $this->assertEquals(serialize(new SerializableClosure($job)), $payload['closure']);
            $this->assertEquals($data, $payload['data']);
        } else {
            $this->assertEquals(
                array(
                    'job'  => $job,
                    'data' => $data,
                ),
                $payload
            );
        }

    }

    /**
     * @covers ::delayed
     * @group  Queue
     */
    public function testDelay()
    {
        $this->assertEquals(
            10,
            $this->queue->delayed(10, 'test', array('test'))
        );
    }
}
