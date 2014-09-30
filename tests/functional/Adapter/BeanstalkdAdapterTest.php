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

use Indigo\Queue\Adapter\BeanstalkdAdapter;
use Indigo\Queue\Job;
use Pheanstalk\Pheanstalk;

/**
 * Tests for BeanstalkdAdapter
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 *
 * @coversDefaultClass Indigo\Queue\Adapter\BeanstalkdAdapter
 * @group              Queue
 * @group              Adapter
 * @group              Beanstalkd
 */
class BeanstalkdAdapterTest extends AbstractMQAdapterTest
{
    public function _before()
    {
        $host = $GLOBALS['beanstalkd_host'];
        $port = $GLOBALS['beanstalkd_port'];

        $pheanstalk = new Pheanstalk($host, $port);

        $this->adapter = new BeanstalkdAdapter($pheanstalk);

        if ($this->adapter->isConnected() === false) {
            $this->markTestSkipped(
                'Beanstalkd connection not available.'
            );

            return;
        }

        $this->adapter->clear('test');
    }

    /**
     * @covers       ::push
     * @dataProvider jobProvider
     */
    public function testPush(Job $job)
    {
        $push = $this->adapter->push('test', $job);

        $this->assertInternalType('integer', $push);
    }

    /**
     * @covers       ::pop
     * @covers       ::delete
     * @covers       ::bury
     * @dataProvider jobProvider
     */
    public function testPop(Job $job)
    {
        $this->adapter->push('test', $job);
        $manager = $this->adapter->pop('test');

        $this->assertInstanceOf(
            'Indigo\\Queue\\Manager\\BeanstalkdManager',
            $manager
        );

        $payload = $manager->getPayload();

        if ($payload['job'] == 'Indigo\\Queue\\Job\\ClosureJob') {
            $this->assertTrue($this->adapter->delete($manager));
        } else {
            $this->assertTrue($this->adapter->bury($manager));
        }
    }
}
