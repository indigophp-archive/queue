<?php

/*
 * This file is part of the Indigo Queue package.
 *
 * (c) Indigo Development Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Test\Unit\Job;

use Indigo\Queue\Job\ClosureJob;
use Jeremeamia\SuperClosure\SerializableClosure;

/**
 * Tests for ClosureJob
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 *
 * @coversDefaultClass Indigo\Queue\Job\ClosureJob
 * @group              Queue
 * @group              Job
 */
class ClosureJobTest extends AbstractJobTest
{
    /**
     * @covers ::execute
     * @covers ::fail
     */
    public function testClosure()
    {
        $config = ['delete' => true, 'test' => true];

        $manager = $this->getManagerMock();

        $manager->shouldReceive('getPayload')
            ->andReturn([
                'closure' => serialize(new SerializableClosure(function() {
                    return true;
                })),
            ]);

        $closure = new ClosureJob;

        $this->assertTrue($closure->execute($manager));
        $this->assertNull($closure->fail($manager));
    }
}
