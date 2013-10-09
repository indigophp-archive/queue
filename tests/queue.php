<?php

$loader = require_once __DIR__ . "/../vendor/autoload.php";
$loader->add('Phresque\\', __DIR__);

require_once 'job.php';

use Phresque\Worker;
use Phresque\Queue\BeanstalkdQueue;


$queue = new BeanstalkdQueue('default', 'localhost');
$queue->push('JobTest1', array(2), 10);