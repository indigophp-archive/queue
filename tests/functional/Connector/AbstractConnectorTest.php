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
}
