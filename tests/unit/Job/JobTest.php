<?php

/*
 * This file is part of the Indigo Queue package.
 *
 * (c) Indigo Development Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Test\Unit\Job;

use Monolog\Logger;
use Monolog\Handler\TestHandler;

/**
 * Tests for AbstractJob
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 *
 * @coversDefaultClass Indigo\Queue\Job\AbstractJob
 * @group              Queue
 * @group              Job
 */
class JobTest extends AbstractJobTest
{
    /**
     * @covers ::fail
     */
	public function testJob()
	{
		$e = new \Exception('message');

		$job = new \AdvancedJob;

        $handler = new TestHandler;
        $logger = new Logger('test');
        $logger->pushHandler($handler);

        $job->setLogger($logger);

        $manager = $this->getManagerMock();

        $this->assertTrue($job->execute($manager));
        $this->assertTrue($job->fail($manager, $e));
		$this->assertTrue($handler->hasError('message'));
	}
}
