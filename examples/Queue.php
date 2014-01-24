<?php

use Indigo\Queue\Connector\BeanstalkdConnector;
use Indigo\Queue\Queue;

$connector = new BeanstalkdConnector('localhost');

$queue = new Queue('test', $connector);

$queue->setLogger(new Psr\Log\NullLogger);

$data = array(
    'name' => 'Test Data'
);

$queue->push('Job', $data);

// Push with a special execute name
$queue->push('Job@exec', $data);

// Push with a special failure name
// Note: you MUST include execute name as well
$queue->push('Job@execute:fail', $data);

// Push a job with a delay
$queue->delayed(2, 'Job', $data);

// You can also push a Closure
// Closure's data array has a special item: config.
// Pass an array for overriding default Closure job config
$queue->push(function (JobInterface $job, array $data) {
    // Do something
}, $data);
