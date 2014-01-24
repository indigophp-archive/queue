<?php

namespace Indigo\Queue\Connector;

use Indigo\Queue\Job\BeanstalkdJob;
use Pheanstalk_Pheanstalk as Pheanstalk;

class BeanstalkdConnectorTest extends ConnectorTest
{
    public function setUp()
    {
        $host = isset($GLOBALS['beanstalkd_host']) ? $GLOBALS['beanstalkd_host'] : 'localhost';
        $port = isset($GLOBALS['beanstalkd_port']) ? $GLOBALS['beanstalkd_port'] : 11300;

        $this->connector = new BeanstalkdConnector($host, $port);

        if (!$this->connector->isConnected()) {
            $this->markTestSkipped(
              'Beanstald connection not available.'
            );
        }
    }

    public function testConnection()
    {
        $this->assertTrue($this->connector->isConnected());
    }

    public function testPheanstalkInstance()
    {
        $pheanstalk = new Pheanstalk('invalid:host');
        $connector = new BeanstalkdConnector($pheanstalk);

        $this->assertFalse($connector->isConnected());
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

    public function testPush()
    {
        $payload = array(
            'job' => 'Job',
            'data' => array(),
            'queue' => 'test'
        );

        $payload = $this->connector->push($payload);
        $this->assertTrue(is_int($payload));
    }

    public function testDelayed()
    {
        $payload = array(
            'job' => 'Job',
            'data' => array(),
            'queue' => 'test'
        );

        $payload = $this->connector->delayed(100, $payload);
        $this->assertTrue(is_int($payload));
    }

    public function testPop()
    {
        if ($job = $this->connector->pop('test')) {
            $this->assertInstanceOf(
                'Indigo\\Queue\\Job\\BeanstalkdJob',
                $job
            );
        } else {
            $this->assertNull($job);
        }

        $this->assertNull($this->connector->pop('null'));
    }
}
