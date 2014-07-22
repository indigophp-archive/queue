<?php

namespace Indigo\Queue;

use Jeremeamia\SuperClosure\SerializableClosure;
use Codeception\TestCase\Test;

/**
 * Tests for Job
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 *
 * @coversDefaultClass Indigo\Queue\Job
 */
class JobTest extends Test
{
    /**
     * Job object
     *
     * @var Indigo\Queue\Job
     */
    protected $job;

    public function _before()
    {
        $this->job = new Job('');
    }

    /**
     * @covers ::_construct
     * @group  Queue
     */
    public function testConstruct()
    {
        $job = new Job('Fake\\Class', ['data'], ['options']);

        $this->assertEquals('Fake\\Class', $job->getJob());
        $this->assertEquals(['data'], $job->getData());
        $this->assertEquals(['options'], $job->getOptions());
    }

    /**
     * @covers ::getJob
     * @covers ::setJob
     * @group  Queue
     */
    public function testJob()
    {
        $this->assertSame($this->job, $this->job->setJob('test'));
        $this->assertEquals('test', $this->job->getJob());
    }

    /**
     * @covers ::getData
     * @covers ::setData
     * @group  Queue
     */
    public function testData()
    {
        $this->assertSame($this->job, $this->job->setData(['test']));
        $this->assertEquals(['test'], $this->job->getData());
    }

    /**
     * @covers ::getOptions
     * @covers ::setOptions
     * @group  Queue
     */
    public function testOptions()
    {
        $this->assertSame($this->job, $this->job->setOptions(['test']));
        $this->assertEquals(['test'], $this->job->getOptions());
    }

    /**
     * @covers ::createPayload
     * @group  Queue
     */
    public function testPayload()
    {
        $job = new Job('Fake\\Class', ['data'], ['options']);

        $this->assertEquals(
            [
                'job'  => 'Fake\\Class',
                'data' => ['data'],
            ],
            $job->createPayload()
        );
    }

    /**
     * @covers ::createPayload
     * @group  Queue
     */
    public function testClosurePayload()
    {
        $closure = function() { return true; };
        $job = new Job($closure, ['data'], ['options']);

        $this->assertEquals(
            [
                'job'     => 'Indigo\\Queue\\ClosureJob',
                'closure' => $closure,
                'data'    => ['data'],
            ],
            $job->createPayload()
        );
    }
}
