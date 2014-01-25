<?php

use Indigo\Queue\Connector\BeanstalkdConnector;
use Pheanstalk_Pheanstalk as Pheanstalk;

$pheanstalk = new Pheanstalk('localhost', 11300);
$connector = new BeanstalkdConnector($pheanstalk);
