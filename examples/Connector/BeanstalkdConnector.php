<?php

use Indigo\Queue\Connector\BeanstalkdConnector;
use Pheanstalk\Pheanstalk;

$pheanstalk = new Pheanstalk('localhost', 11300);
$connector = new BeanstalkdConnector($pheanstalk);
