<?php

namespace Indigo\Queue\Test\Connector;

use Indigo\Queue\Connector\BeanstalkdConnector;
use Indigo\Queue\Job\BeanstalkdJob;
use Pheanstalk_Pheanstalk as Pheanstalk;

class BeanstalkdConnectorTest extends ConnectorTest
{
    public function setUp()
    {
        $host = isset($GLOBALS['beanstalkd_host']) ? $GLOBALS['beanstalkd_host'] : 'localhost';
        $port = isset($GLOBALS['beanstalkd_port']) ? $GLOBALS['beanstalkd_port'] : 11300;

        $pheanstalk = new Pheanstalk($host, $port);

        $this->connector = new BeanstalkdConnector($pheanstalk);

        if (!$this->connector->isConnected()) {
            $this->markTestSkipped(
                'Beanstalkd connection not available.'
            );
        }
    }

    public function testConnection()
    {
        $this->assertTrue($this->connector->isConnected());
    }

    public function testPheanstalk()
    {
        $pheanstalk = $this->connector->getPheanstalk();

        $this->assertInstanceOf('Pheanstalk_Pheanstalk', $pheanstalk);

        $this->assertInstanceOf(
            'Indigo\\Queue\\Connector\\BeanstalkdConnector',
            $this->connector->setPheanstalk($pheanstalk)
        );
    }

    /**
     * @dataProvider payloadProvider
     */
    public function testPush($payload)
    {
        $push = $this->connector->push('test', $payload);
        $this->assertTrue(is_int($push));
    }

    /**
     * @dataProvider payloadProvider
     */
    public function testDelayed($payload)
    {
        $payload = $this->connector->delayed('test', 100, $payload);
        $this->assertTrue(is_int($payload));
    }

    /**
     * @dataProvider payloadProvider
     */
    public function testPop($payload)
    {
        $this->connector->push('test_pop', $payload);

        if ($job = $this->connector->pop('test_pop')) {
            $this->assertInstanceOf(
                'Indigo\\Queue\\Job\\BeanstalkdJob',
                $job
            );

            $this->assertEquals($payload, $job->getPayload());

            if ($payload['job'] == 'Job@runThis') {
                $this->assertTrue($this->connector->delete($job));
            } else {
                $this->assertTrue($this->connector->bury($job));
            }
        } else {
            $this->assertNull($job);
        }

        $this->assertNull($this->connector->pop('null'));
    }

    /**
     * @dataProvider payloadProvider
     */
    public function testRelease($payload)
    {
        $this->connector->push('test_release', $payload);

        if ($job = $this->connector->pop('test_release')) {
            $this->assertInstanceOf(
                'Indigo\\Queue\\Job\\BeanstalkdJob',
                $job
            );

            $this->assertTrue($this->connector->release($job));
        } else {
            $this->assertNull($job);
        }

        $job = $this->connector->pop('test_release');
        $this->connector->delete($job);
    }

    public function tearDown()
    {
        while (($job = $this->connector->pop('test')) instanceof BeanstalkdJob) {
            $this->connector->delete($job);
        }
    }
}
