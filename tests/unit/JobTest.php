<?php

/*
 * This file is part of the Indigo Queue package.
 *
 * (c) Indigo Development Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Indigo\Queue;

use Jeremeamia\SuperClosure\SerializableClosure;
use Codeception\TestCase\Test;

/**
 * Tests for Job
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 *
 * @coversDefaultClass Indigo\Queue\Job
 * @group              Queue
 * @group              Main
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
     * @covers ::__construct
     * @covers ::createFromPayload
     */
    public function testConstruct()
    {
        $job = new Job('Fake\\Class', ['data'], ['options'], ['extras']);

        $this->assertEquals('Fake\\Class', $job->getJob());
        $this->assertEquals(['data'], $job->getData());
        $this->assertEquals(['options'], $job->getOptions());
        $this->assertEquals(['extras'], $job->getExtras());

        $job = Job::createFromPayload($job->createPayload());

        $this->assertEquals('Fake\\Class', $job->getJob());
        $this->assertEquals(['data'], $job->getData());
        $this->assertEquals(['extras'], $job->getExtras());
    }

    /**
     * @covers ::getJob
     * @covers ::setJob
     */
    public function testJob()
    {
        $this->assertSame($this->job, $this->job->setJob('test'));
        $this->assertEquals('test', $this->job->getJob());
    }

    /**
     * @covers ::getData
     * @covers ::setData
     */
    public function testData()
    {
        $this->assertSame($this->job, $this->job->setData(['test']));
        $this->assertEquals(['test'], $this->job->getData());
    }

    /**
     * @covers ::getOptions
     * @covers ::setOptions
     */
    public function testOptions()
    {
        $this->assertSame($this->job, $this->job->setOptions(['test']));
        $this->assertEquals(['test'], $this->job->getOptions());
    }

    /**
     * @covers ::getExtras
     * @covers ::setExtras
     */
    public function testExtras()
    {
        $this->assertSame($this->job, $this->job->setExtras(['test']));
        $this->assertEquals(['test'], $this->job->getExtras());
    }

    /**
     * @covers ::createPayload
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
     */
    public function testClosurePayload()
    {
        $closure = function() { return true; };
        $job = new Job($closure, ['data'], ['options']);

        $this->assertEquals(
            [
                'job'     => 'Indigo\\Queue\\Job\\ClosureJob',
                'closure' => serialize(new SerializableClosure($closure)),
                'data'    => ['data'],
            ],
            $job->createPayload()
        );
    }
}
