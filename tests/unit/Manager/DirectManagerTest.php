<?php

namespace Test\Unit;

use Indigo\Queue\Manager\DirectManager;

/**
 * Tests for DirectManager
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 *
 * @coversDefaultClass Indigo\Queue\Manager\DirectManager
 * @group              Queue
 * @group              Manager
 * @group              Direct
 */
class DirectManagerTest extends AbstractManagerTest
{
    public function _before()
    {
        $this->connector = \Mockery::mock('Indigo\\Queue\\Connector\\DirectConnector');

        $this->manager = new DirectManager('test', ['payload'], $this->connector);
    }

    /**
     * @covers ::__construct
     */
    public function testConstruct()
    {
        $manager = new DirectManager('test', ['payload'], $this->connector);

        $this->assertEquals('test', $manager->getQueue());
        $this->assertEquals(['payload'], $manager->getPayload());
        $this->assertSame($this->connector, $manager->getConnector());
    }

    /**
     * @covers ::attempts
     */
    public function testAttempts()
    {
        $this->assertEquals(1, $this->manager->attempts());
    }
}