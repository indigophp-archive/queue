<?php

namespace Phresque;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

abstract class LoggerEventAbstract implements LoggerAwareInterface
{
    /**
     * Logger instance
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Event handler object
     *
     * @var callable
     */
    protected $eventHandler;

    /**
     * Gets a logger instance from the object
     *
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * Sets a logger instance on the object
     *
     * @param LoggerInterface $logger
     * @return null
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Sets an event handler on the object
     *
     * @param callable $handler
     */
    public function setEventHandler(callable $handler)
    {
        $this->eventHandler = $handler;
    }

    /**
     * Triggers an event of the current event handler
     *
     * @param  string $event Event name
     * @param  array  $data  Data passed to listeners
     * @return void
     */
    public function trigger($event, $data = array())
    {
        call_user_func_array($this->eventHandler, array($event, $data));
    }
}