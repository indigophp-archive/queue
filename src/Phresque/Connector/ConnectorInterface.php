<?php

namespace Phresque\Connector;

interface ConnectorInterface
{
    /**
    * Establish a queue connection.
    *
    * @param array $config
    * @return \Illuminate\Queue\QueueInterface
    */
    public function connect(array $config);
}
