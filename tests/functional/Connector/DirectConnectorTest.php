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

use Indigo\Queue\Connector\DirectConnector;
use Indigo\Queue\Job;

/**
 * Tests for DirectConnector
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 *
 * @coversDefaultClass Indigo\Queue\Connector\DirectConnector
 * @group              Queue
 * @group              Connector
 * @group              Direct
 */
class DirectConnectorTest extends AbstractConnectorTest
{
    public function _before()
    {
        $this->connector = new DirectConnector;
    }

    /**
     * @covers       ::push
     * @covers       ::pop
     * @dataProvider jobProvider
     */
    public function testPush(Job $job)
    {
        $this->assertTrue($this->connector->push('test', $job));
    }

    /**
     * @covers       ::push
     * @covers       ::pop
     * @covers       ::delayed
     * @dataProvider jobProvider
     */
    public function testDelayed(Job $job)
    {
        $this->assertTrue($this->connector->delayed('test', 0.5, $job));
    }
}
