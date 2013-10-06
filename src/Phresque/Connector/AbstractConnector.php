<?php

namespace Phresque\Connector;

abstract class AbstractConnector implements ConnectorInterface
{
    /**
     * Variable holding client object
     *
     * @var object
     */
    protected $client;

    public function __call($method, $params)
    {
        $callable = array($this->client, $method);
        return call_user_func_array($callable, $params);
    }

    public function getClient()
    {
        return $this->client;
    }

    public function setClient($client)
    {
        $this->client = $client;
    }
}
