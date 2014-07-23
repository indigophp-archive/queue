<?php

namespace Test\Unit;

use Indigo\Queue\Manager\IronManager;

/**
 * Tests for IronManager
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 *
 * @coversDefaultClass Indigo\Queue\Manager\IronManager
 * @group              Queue
 * @group              Manager
 * @group              Iron
 */
class IronManagerTest extends AbstractManagerTest
{
    public function _before()
    {
        $this->ironJob = new \stdClass;

        $this->ironJob->body = json_encode([]);
        $this->ironJob->reserved_count = 1;

        $this->connector = \Mockery::mock('Indigo\\Queue\\Connector\\IronConnector');

        $this->manager = new IronManager('test', $this->ironJob, $this->connector);
    }

    /**
     * @covers ::__construct
     */
    public function testConstruct()
    {
        $manager = new IronManager('test', $this->ironJob, $this->connector);

        $this->assertSame($this->ironJob, $this->manager->getIronJob());
    }

    /**
     * @covers ::attempts
     */
    public function testAttempts()
    {
        $this->assertEquals(1, $this->manager->attempts());
    }

    /**
     * @covers ::getIronJob
     */
    public function testIronJob()
    {
        $this->assertSame($this->ironJob, $this->manager->getIronJob());
    }
}
