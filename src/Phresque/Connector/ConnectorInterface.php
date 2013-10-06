<?php

namespace Phresque\Connector;

interface ConnectorInterface
{
    /**
    * Establish a queue connection.
    *
    * @param array $config
    * @return \Phresque\Connector\ConnectorInterface
    */
    public function connect(array $config);

}
