<?php

namespace Indigo\Queue;

use Jeremeamia\SuperClosure\SerializableClosure;
use Codeception\TestCase\Test;

/**
 * Tests for Closure
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 *
 * @coversDefaultClass Indigo\Queue\Closure
 */
class ClosureTest extends Test
{
    protected $job;

    public function _before()
    {
        $this->job = \Mockery::mock('Indigo\\Queue\\Job\\JobInterface');
        $this->job->shouldReceive('getPayload')
            ->andReturn([
                'closure' => serialize(new SerializableClosure(function() {
                    return true;
                })),
            ]);
    }

    /**
     * @covers ::__construct
     * @covers ::execute
     * @group  Queue
     */
    public function testClosure()
    {
        $config = ['delete' => true, 'test' => true];

        $closure = new Closure($this->job, ['config' => $config]);

        $this->assertEquals($config, $closure->config);

        $this->assertTrue($closure->execute($this->job, []));
    }
}
