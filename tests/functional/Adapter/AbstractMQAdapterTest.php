<?php

/*
 * This file is part of the Indigo Queue package.
 *
 * (c) Indigo Development Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Test\Functional;

use Indigo\Queue\Job;

/**
 * Tests for MQ's Adapter
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
abstract class AbstractMQAdapterTest extends AbstractAdapterTest
{
    public function _after()
    {
        if (!is_null($this->adapter)) {
            $this->adapter->clear('test');
        }
    }

    /**
     * Pushes all jobs in the provider to the queue at once
     *
     * @return Job[]
     */
    public function pushJobs($queue = 'test')
    {
        $jobs = $this->jobProvider();

        foreach ($jobs as $job) {
            $job = reset($job);

            $this->adapter->push($queue, $job);
        }

        return $jobs;
    }

    /**
     * @covers       ::pop
     * @covers       ::delete
     * @dataProvider jobProvider
     */
    public function testPop(Job $job)
    {
        $this->adapter->push('test', $job);

        $manager = $this->adapter->pop('test');

        $this->assertInstanceOf(
            $this->adapter->getManagerClass(),
            $manager
        );

        $this->assertTrue($this->adapter->delete($manager));
    }

    /**
     * @covers ::count
     */
    public function testCount()
    {
        $this->adapter->clear('test_count');
        $count = (int) $this->adapter->count('test_count');

        $jobs = $this->pushJobs('test_count');

        $this->assertEquals($count + count($jobs), $this->adapter->count('test_count'));

        $this->adapter->clear('test_count');
    }

    /**
     * @covers ::clear
     */
    public function testClear()
    {
        $this->assertTrue($this->adapter->clear('test_clear'));
        $this->assertEquals(0, $this->adapter->count('test_clear'));
    }

    /**
     * @covers       ::pop
     * @covers       ::release
     * @dataProvider jobProvider
     */
    public function testRelease(Job $job)
    {
        $this->adapter->push('test', $job);

        $manager = $this->adapter->pop('test');

        $this->assertTrue($this->adapter->release($manager));
    }
}
