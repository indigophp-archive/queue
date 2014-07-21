<?php

namespace Indigo\Queue\Test\Job;

use Jeremeamia\SuperClosure\SerializableClosure;

abstract class JobTest extends \PHPUnit_Framework_TestCase
{
    protected $connector;

    public function tearDown()
    {
        \Mockery::close();
    }

    public function payloadProvider()
    {
        return array(
            array(array(
                'job' => 'Job',
                'data' => array(),
            ), true),
            array(array(
                'job' => 'Job@runThis',
                'data' => array(),
            ), true),
            array(array(
                'job' => 'Job@failThis',
                'data' => array(),
            ), null),
            array(array(
                'job' => 'Job@fake',
                'data' => array(),
            ), false),
            array(array(
                'job' => 'Fake',
                'data' => array(),
            ), false),
            array(array(
                'job' => 'Job@failThis:failedThis',
                'data' => array(),
            ), null),
            array(array(
                'job' => 'Job@failThis:nonExistent',
                'data' => array(),
            ), null),
            array(array(
                'job' => 'Indigo\\Queue\\Closure',
                'data' => array(),
                'closure' => serialize(new SerializableClosure(function () {
                    return true;
                })),
            ), true),
        );
    }
}
