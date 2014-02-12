<?php

namespace Indigo\Queue\Test\Connector;

use Jeremeamia\SuperClosure\SerializableClosure;

abstract class ConnectorTest extends \PHPUnit_Framework_TestCase
{
    protected $connector;
    protected $queue;

    public function payloadProvider()
    {
        return array(
            array(array(
                'job' => 'Job@runThis',
                'data' => array(),
            )),
            array(array(
                'job' => 'Indigo\\Queue\\Closure',
                'data' => array(),
                'closure' => serialize(new SerializableClosure(function () {
                    return true;
                })),
            )),
        );
    }

    public function testConnection()
    {
        $this->assertTrue($this->connector->isConnected());
    }
}
