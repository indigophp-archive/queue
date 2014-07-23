<?php

namespace Test\Functional;

use Indigo\Queue\Connector\BeanstalkdConnector;
use Indigo\Queue\Job;
use Pheanstalk\Pheanstalk;

/**
 * Tests for BeanstalkdConnector
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 *
 * @coversDefaultClass Indigo\Queue\Connector\BeanstalkdConnector
 * @group              Queue
 * @group              Connector
 * @group              Beanstalkd
 */
class BeanstalkdConnectorTest extends AbstractMQConnectorTest
{
    public function _before()
    {

        $host = $GLOBALS['beanstalkd_host'];
        $port = $GLOBALS['beanstalkd_port'];

        $pheanstalk = new Pheanstalk($host, $port);

        $this->connector = new BeanstalkdConnector($pheanstalk);

        if ($this->connector->isConnected() === false) {
            $this->markTestSkipped(
                'Beanstalkd connection not available.'
            );

            return;
        }

        $this->connector->clear('test');
    }

    /**
     * @covers       ::push
     * @dataProvider jobProvider
     */
    public function testPush(Job $job)
    {
        $push = $this->connector->push('test', $job);

        $this->assertInternalType('integer', $push);
    }

    /**
     * @covers       ::delayed
     * @covers       ::push
     * @dataProvider jobProvider
     */
    public function testDelayed(Job $job)
    {
        $delayed = $this->connector->delayed('test', 100, $job);

        $this->assertInternalType('integer', $delayed);
    }

    /**
     * @covers       ::pop
     * @covers       ::delete
     * @covers       ::bury
     * @dataProvider jobProvider
     */
    public function testPop(Job $job)
    {
        $this->connector->push('test', $job);
        $manager = $this->connector->pop('test');

        $this->assertInstanceOf(
            'Indigo\\Queue\\Manager\\BeanstalkdManager',
            $manager
        );

        $payload = $manager->getPayload();

        if ($payload['job'] == 'Indigo\\Queue\\Job\\ClosureJob') {
            $this->assertTrue($this->connector->delete($manager));
        } else {
            $this->assertTrue($this->connector->bury($manager));
        }
    }
}
