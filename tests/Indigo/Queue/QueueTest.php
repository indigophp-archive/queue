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
                    ->andReturnUsing(function (array $payload) {
                        return $payload;
                    });

                $mock->shouldReceive('delayed')
                    ->andReturnUsing(function ($delay) {
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

    public function testPush()
    {
        $payload = array(
            'job'  => 'test',
            'data' => array(
                'test'
            ),
            'queue' => 'test',
        );

        $this->assertEquals(
            $payload,
            $this->queue->push('test', array('test'))
        );
    }

    public function testClosure()
    {
        $closure = function () {
            return true;
        };

        $payload = array(
            'job'  => 'Indigo\\Queue\\Closure',
            'data' => array(
                'test'
            ),
            'closure' => serialize(new SerializableClosure($closure)),
            'queue' => 'test',
        );

        $this->assertEquals(
            $payload,
            $this->queue->push($closure, array('test'))
        );
    }

    public function testDelay()
    {
        $this->assertEquals(
            10,
            $this->queue->delayed(10, 'test', array('test'))
        );
    }
}
