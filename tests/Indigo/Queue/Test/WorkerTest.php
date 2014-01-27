<?php

namespace Indigo\Queue\Test;

use Indigo\Queue\Worker;

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
                            'Indigo\\Queue\\Job\\JobInterface',
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

    public function testWork()
    {
        $this->assertNull($this->worker->work());
        $this->assertTrue($this->worker->work());
    }
}
