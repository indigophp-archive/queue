<?php

namespace Indigo\Queue;

use Jeremeamia\SuperClosure\SerializableClosure;

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

    public function testInstance()
    {
        $queue = new Queue('test', $this->queue->getConnector());
        $this->assertEquals('test', (string)$queue);
    }

    public function testQueue()
    {
        $this->assertEquals('test', $this->queue->getQueue());
        $this->assertEquals('test', (string)$this->queue);
    }

    public function testLogger()
    {
        $logger = $this->queue->getLogger();

        $this->assertInstanceOf(
            'Psr\\Log\\LoggerInterface',
            $logger
        );

        $this->assertNull($this->queue->setLogger($logger));
    }

    public function testConnector()
    {
        $connector = $this->queue->getConnector();

        $this->assertInstanceOf(
            'Indigo\\Queue\\Connector\\ConnectorInterface',
            $connector
        );

        $this->assertInstanceOf(
            'Indigo\\Queue\\Queue',
            $this->queue->setConnector($connector)
        );
    }

    /**
     * @dataProvider jobProvider
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

    public function testDelay()
    {
        $this->assertEquals(
            10,
            $this->queue->delayed(10, 'test', array('test'))
        );
    }
}
