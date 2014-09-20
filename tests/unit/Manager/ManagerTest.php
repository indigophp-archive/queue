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
 * Tests for AbstractManager
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 *
 * @coversDefaultClass Indigo\Queue\Manager\AbstractManager
 */
class ManagerTest extends AbstractManagerTest
{
    public function _before()
    {
        $this->connector = \Mockery::mock('Indigo\\Queue\\Connector');
        $this->manager = new \DummyManager('test', $this->connector);
    }

    /**
     * @covers ::getConnector
     * @group  Queue
     */
    public function testConnector()
    {
        $this->assertSame($this->connector, $this->manager->getConnector());
    }

    /**
     * @covers ::getPayload
     * @group  Queue
     */
    public function testPayload()
    {
        $this->assertEquals([], $this->manager->getPayload());
    }

    /**
     * @covers ::getQueue
     * @group  Queue
     */
    public function testQueue()
    {
        $this->assertEquals('test', $this->manager->getQueue());
    }

    /**
     * @covers ::attempts
     * @group  Queue
     */
    public function testAttempts()
    {
        $this->assertEquals(1, $this->manager->attempts());
    }
}
