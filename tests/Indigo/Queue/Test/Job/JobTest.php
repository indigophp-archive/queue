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
                'job' => 'Indigo\\Queue\\Closure',
                'data' => array(),
                'closure' => serialize(new SerializableClosure(function () {
                    return true;
                })),
            ), true),
        );
    }

    // public function testPayload()
    // {
    //     $payload = $this->job->getPayload();

    //     $this->assertTrue(is_array($payload));
    //     $this->assertArrayHasKey('job', $payload);
    //     $this->assertArrayHasKey('data', $payload);

    //     if ($payload['job'] == 'Indigo\\Queue\\Closure') {
    //         $this->assertArrayHasKey('closure', $payload);
    //     }

    //     $this->assertEquals($payload, $this->job->getPayload());
    // }

    // public function jobProvider()
    // {
    //     return array(
    //         array('Job@exec:fail', array('Job', 'exec', 'fail')),
    //         array('Job@exec', array('Job', 'exec', 'failure')),
    //         array('Job', array('Job', 'execute', 'failure')),
    //         array('Job:exec@fail', array('Job', 'exec', 'fail')),
    //         array(':exec@fail', array(null, 'exec', 'fail')),
    //     );
    // }

    // /**
    //  * @dataProvider jobProvider
    //  */
    // public function testParseJob($rawJob, $parsedJob)
    // {
    //     $method = new \ReflectionMethod(get_class($this->job), 'parseJob');

    //     $method->setAccessible(true);

    //     $this->assertEquals($parsedJob, $method->invoke($this->job, $rawJob));
    // }
}
