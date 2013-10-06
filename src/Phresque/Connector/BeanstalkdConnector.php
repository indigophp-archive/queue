<?php

namespace Phresque\Connector;

use Pheanstalk_Pheanstalk as Pheanstalk;

class BeanstalkdConnector extends AbstractConnector implements ConnectorInterface
{
    public function __construct($connection = array())
    {
        if ($connection instanceof Pheanstalk)
        {
            $this->connection = $connection;
        }
        elseif(is_array($connection))
        {
            $this->connection = $this->connect($connection);
        }
    }

    public function connect(array $config)
    {
        $host = isset($config['host']) ? $config['host'] : 'localhost';
        $port = isset($config['port']) ? $config['port'] : '11300';
        return new Pheanstalk($host, $port);
    }
}