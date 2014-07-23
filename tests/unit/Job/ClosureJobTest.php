<?php

namespace Test\Unit;

use Indigo\Queue\Job\ClosureJob;
use Jeremeamia\SuperClosure\SerializableClosure;
use Codeception\TestCase\Test;

/**
 * Tests for ClosureJob
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 *
 * @coversDefaultClass Indigo\Queue\Job\ClosureJob
 * @group              Queue
 * @group              Job
 */
class ClosureJobTest extends Test
{
    /**
     * Manager mock
     *
     * @var ManagerInterface
     */
    protected $manager;

    public function _before()
    {
        $this->manager = \Mockery::mock('Indigo\\Queue\\Manager\\ManagerInterface');
        $this->manager->shouldReceive('getPayload')
            ->andReturn([
                'closure' => serialize(new SerializableClosure(function() {
                    return true;
                })),
            ]);
    }

    /**
     * @covers ::execute
     * @covers ::fail
     */
    public function testClosure()
    {
        $config = ['delete' => true, 'test' => true];

        $closure = new ClosureJob;

        $this->assertTrue($closure->execute($this->manager));
        $this->assertNull($closure->fail($this->manager));
    }
}
