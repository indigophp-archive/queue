<?php

namespace Phresque\Connector;

abstract class AbstractConnector
{
    /**
     * Variable holding connection object
     *
     * @var object
     */
    protected $connection;

    public function __call($method, $params)
    {
        $callable = array($this->connection, $method);
        return call_user_func_array($callable, $params);
    }

}