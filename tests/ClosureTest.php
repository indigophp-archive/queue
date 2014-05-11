<?php

namespace Indigo\Queue\Test;

use Indigo\Queue\Closure;
use Jeremeamia\SuperClosure\SerializableClosure;

/**
 * Tests for Closure
 *
 * @author  Márk Sági-Kazár <mark.sagikazar@gmail.com>
 *
 * @coversDefaultClass  Indigo\Queue\Closure
 */
class ClosureTest extends \PHPUnit_Framework_TestCase
{
    protected $job;

    public function setUp()
    {
        $this->job = \Mockery::mock(
            'Indigo\\Queue\\Job\\JobInterface',
            function ($mock)
            {
                $mock->shouldReceive('getPayload')
                    ->andReturn(array(
                        'closure' => serialize(new SerializableClosure(function() { return true; }))
                    ));
            }
        );
    }

    public function tearDown()
    {
        \Mockery::close();
    }

    /**
     * @covers ::__construct
     * @covers ::execute
     * @group  Queue
     */
    public function testClosure()
    {
        $closure = new Closure($this->job, array('config' => array('test' => true)));

        $this->assertEquals(array('delete' => true, 'test' => true), $closure->config);

        $this->assertTrue($closure->execute($this->job, array()));
    }
}
