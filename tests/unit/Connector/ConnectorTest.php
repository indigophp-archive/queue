<?php

namespace Indigo\Queue\Test\Connector;

use Jeremeamia\SuperClosure\SerializableClosure;

require __DIR__.'/../../resources/Job.php';

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

    /**
     * @covers ::isConnected
     * @group  Queue
     */
    public function testConnection()
    {
        $this->assertTrue($this->connector->isConnected());
    }
}
