<?php

use Indigo\Queue\Connector\IronConnector;
use IronMQ;

$iron = new IronMQ(array(
    'token_id' => 'YOUR_TOKEN_HERE',
    'project_id' => 'YOUR_PROJECT_HERE',
));

$connector = new IronConnector($iron);
