<?php

namespace Indigo\Queue\Test;

use Indigo\Queue\Worker;

/**
 * Tests for Worker
 *
 * @author  Márk Sági-Kazár <mark.sagikazar@gmail.com>
 *
 * @coversDefaultClass  Indigo\Queue\Worker
 */
class WorkerTest extends \PHPUnit_Framework_TestCase
{
    protected $worker;

    public function setUp()
    {
        $connector = \Mockery::mock(
            'Indigo\\Queue\\Connector\\ConnectorInterface',
            function ($mock)
            {
                $mock->shouldReceive('pop')
                    ->andReturn(
                        null,
                        \Mockery::mock(
                            'Indigo\\Queue\\Job\\JobInterface, Psr\\Log\\LoggerAwareInterface',
                            function ($mock) {
                                $mock->shouldReceive('execute')
                                    ->andReturn(true);

                                $mock->shouldReceive('setLogger')
                                    ->andReturn(null);
                            }
                        )
                    );
            }
        );

        $this->worker = new Worker('test', $connector);
    }

    public function tearDown()
    {
        \Mockery::close();
    }

    /**
     * @covers ::__construct
     * @group  Queue
     */
    public function testConstruct()
    {
        $connector = \Mockery::mock('Indigo\\Queue\\Connector\\ConnectorInterface');
        $worker = new Worker('test', $connector);
    }

    /**
     * @covers            ::__construct
     * @expectedException InvalidArgumentException
     * @group             Queue
     */
    public function testConstructFailure()
    {
        $connector = \Mockery::mock('Indigo\\Queue\\Connector\\DirectConnector');
        $worker = new Worker('test', $connector);
    }

    /**
     * @covers ::work
     * @covers ::getJob
     * @group  Queue
     */
    public function testWork()
    {
        $this->assertNull($this->worker->work());
        $this->assertTrue($this->worker->work());
    }
}
