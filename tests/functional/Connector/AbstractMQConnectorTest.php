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
        $jobs = $this->pushJobs();

        $this->assertEquals(count($jobs), $this->connector->count('test'));
    }

    /**
     * @covers ::clear
     */
    public function testClear()
    {
        $jobs = $this->pushJobs();

        $this->assertEquals(count($jobs), $this->connector->count('test'));
        $this->assertTrue($this->connector->clear('test'));
        $this->assertEquals(0, $this->connector->count('test'));
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
