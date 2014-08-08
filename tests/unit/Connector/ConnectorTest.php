<?php

/*
 * This file is part of the Indigo Queue package.
 *
 * (c) Indigo Development Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Test\Unit;

/**
 * Tests for AbstractConnector
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 *
 * @coversDefaultClass Indigo\Queue\Connector\AbstractConnector
 * @group              Queue
 * @group              Connector
 */
class ConnectorTest extends AbstractConnectorTest
{
    public function _before()
    {
        $this->connector = new \DummyConnector;
    }

    /**
     * @covers ::getManagerClass
     */
    public function testManager()
    {
        $this->assertEquals('Fake\\Class', $this->connector->getManagerClass());
    }
}
