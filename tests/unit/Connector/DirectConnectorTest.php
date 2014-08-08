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

use Indigo\Queue\Connector\DirectConnector;

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
     * @covers ::isConnected
     */
    public function testConnection()
    {
        $this->assertTrue($this->connector->isConnected());
    }

    /**
     * @covers                   ::pop
     * @covers                   Indigo\Queue\Exception\QueueEmptyException
     * @expectedException        Indigo\Queue\Exception\QueueEmptyException
     * @expectedExceptionMessage Queue test is empty.
     */
    public function testEmptyPop()
    {
        $this->connector->pop('test');
    }

    /**
     * @covers ::count
     */
    public function testCount()
    {
        $this->assertEquals(1, $this->connector->count(''));
    }

    /**
     * @covers ::delete
     */
    public function testDelete()
    {
        $this->assertTrue($this->connector->delete($this->getManagerMock()));
    }

    /**
     * @covers ::clear
     */
    public function testClear()
    {
        $this->assertTrue($this->connector->clear(''));
    }

    /**
     * @covers ::release
     */
    public function testRelease()
    {
        $this->assertTrue($this->connector->release($this->getManagerMock()));
    }
}
