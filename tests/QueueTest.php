<?php

namespace Indigo\Queue\Test;

use Indigo\Queue\Queue;
use Jeremeamia\SuperClosure\SerializableClosure;

/**
 * Tests for Queue
 *
 * @author  Márk Sági-Kazár <mark.sagikazar@gmail.com>
 *
 * @coversDefaultClass  Indigo\Queue\Queue
 */
class QueueTest extends \PHPUnit_Framework_TestCase
{
    protected $queue;

    public function setUp()
    {
        $connector = \Mockery::mock(
            'Indigo\\Queue\\Connector\\ConnectorInterface',
            function ($mock)
            {
                $mock->shouldReceive('push')
                    ->andReturnUsing(function ($queue, array $payload) {
                        return $payload;
                    });

                $mock->shouldReceive('delayed')
                    ->andReturnUsing(function ($queue, $delay) {
                        return $delay;
                    });
            }
        );

        $this->queue = new Queue('test', $connector);
    }

    public function tearDown()
    {
        \Mockery::close();
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

        $this->assertInstanceOf(
            'Indigo\\Queue\\Connector\\ConnectorInterface',
            $connector
        );

        $this->assertEquals(
            $this->queue,
            $this->queue->setConnector($connector)
        );
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
