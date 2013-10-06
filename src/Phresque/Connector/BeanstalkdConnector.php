<?php

namespace Phresque\Connector;

use Pheanstalk_Pheanstalk as Pheanstalk;

class BeanstalkdConnector extends AbstractConnector
{
    public function __construct($host, $port = Pheanstalk::DEFAULT_PORT)
    {
        if ($host instanceof Pheanstalk) {
            $this->connection = $host;
        } else {
            $this->connect(array('host' => $host, 'port' => $port));
        }
    }

    public function connect(array $config)
    {
        $this->client =  new Pheanstalk($config['host'], $config['port']);
    }

    public function setClient(Pheanstalk $client)
    {
        $this->client = $client;
    }
}
