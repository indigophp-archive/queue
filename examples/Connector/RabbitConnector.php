<?php

use Indigo\Queue\Connector\RabbitConnector;
use PhpAmqpLib\Connection\AMQPStreamConnection;

$amqp = new AMQPStreamConnection('localhost', '5672', 'guest', 'guest');
$connector = new RabbitConnector($amqp);
