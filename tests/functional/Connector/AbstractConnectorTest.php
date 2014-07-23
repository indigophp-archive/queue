<?php

namespace Test\Functional;

use Indigo\Queue\Job;
use Codeception\TestCase\Test;

/**
 * Tests for ConnectorInterface
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
abstract class AbstractConnectorTest extends Test
{
    /**
     * Connector object
     *
     * @var ConnectorInterface
     */
    protected $connector;

    public function _after()
    {
        $this->connector->clear('test');
    }

    /**
     * Provides jobs
     *
     * @return Job[]
     */
    public function jobProvider()
    {
        return [
            [new Job('DummyJob')],
            [new Job(function () {
                return true;
            })],
        ];
    }

    /**
     * Pushes all jobs in the provider to the queue at once
     *
     * @return Job[]
     */
    public function pushJobs()
    {
        $jobs = $this->jobProvider();

        foreach ($jobs as $job) {
            $job = reset($job);

            $this->connector->push('test', $job);
        }

        return $jobs;
    }
}
