<?php

namespace Indigo\Queue\Job;

/**
 * Abstract Job Test
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
abstract class JobTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Job
     *
     * @var JobInterface
     */
    protected $job;

    /**
     * Connector
     *
     * @var ConnectorInterface
     */
    protected $connector;

    public function setUp()
    {
        $this->connector = \Mockery::mock('Indigo\\Queue\\Connector\\ConnectorInterface');

        $this->worker = new Worker('test', $this->connector);

        $this->queue = new Queue('test', $this->connector);
    }

    public function tearDown()
    {
        \Mockery::close();
    }

    public function testPayload()
    {
        $payload = $this->job->getPayload();

        $this->assertTrue(is_array($payload));
        $this->assertArrayHasKey('job', $payload);
        $this->assertArrayHasKey('data', $payload);

        if ($payload['job'] == 'Indigo\\Queue\\Closure') {
            $this->assertArrayHasKey('closure', $payload);
        }

        $this->assertEquals($payload, $this->job->getPayload());
    }

    public function jobProvider()
    {
        return array(
            array('Job@exec:fail', array('Job', 'exec', 'fail')),
            array('Job@exec', array('Job', 'exec', 'failure')),
            array('Job', array('Job', 'execute', 'failure')),
            array('Job:exec@fail', array('Job', 'exec', 'fail')),
            array(':exec@fail', array(null, 'exec', 'fail')),
        );
    }

    /**
     * @dataProvider jobProvider
     */
    public function testParseJob($rawJob, $parsedJob)
    {
        $method = new \ReflectionMethod(get_class($this->job), 'parseJob');

        $method->setAccessible(true);

        $this->assertEquals($parsedJob, $method->invoke($this->job, $rawJob));
    }

    public function testLogger()
    {
        $logger = $this->job->getLogger();

        $this->assertInstanceOf(
            'Psr\\Log\\LoggerInterface',
            $logger
        );

        $this->assertNull($this->job->setLogger($logger));
    }
}
