<?php

namespace Test\Functional;

use Indigo\Queue\Job;

/**
 * Tests for MQ's ConnectorInterface
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
abstract class AbstractMQConnectorTest extends AbstractConnectorTest
{
    public function _after()
    {
        $this->connector->clear('test');
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

            $this->connector->push($queue, $job);
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
        $this->connector->push('test', $job);

        $manager = $this->connector->pop('test');

        $this->assertInstanceOf(
            $this->connector->getManagerClass(),
            $manager
        );

        $this->assertTrue($this->connector->delete($manager));
    }

    /**
     * @covers ::count
     */
    public function testCount()
    {
        $this->connector->clear('test_count');

        $jobs = $this->pushJobs('test_count');

        $this->assertEquals(count($jobs), $this->connector->count('test_count'));

        $this->connector->clear('test_count');
    }

    /**
     * @covers ::clear
     */
    public function testClear()
    {
        $this->assertTrue($this->connector->clear('test_clear'));
        $this->assertEquals(0, $this->connector->count('test_clear'));
    }

    /**
     * @covers       ::pop
     * @covers       ::release
     * @dataProvider jobProvider
     */
    public function testRelease(Job $job)
    {
        $this->connector->push('test', $job);

        $manager = $this->connector->pop('test');

        $this->assertTrue($this->connector->release($manager));
    }
}
