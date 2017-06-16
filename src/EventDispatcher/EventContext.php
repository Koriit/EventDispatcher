<?php

/**
 * @copyright 2017 Aleksander Stelmaczonek <al.stelmaczonek@gmail.com>
 * @license MIT License, see license file distributed with this source code
 */

namespace Koriit\EventDispatcher;

/**
 * @author Aleksander Stelmaczonek <al.stelmaczonek@gmail.com>
 */
class EventContext implements EventContextInterface
{
    /**
     * @var bool
     */
    protected $stopped = false;

    /**
     * @var mixed
     */
    protected $stopValue;

    /**
     * @var bool
     */
    protected $ignoreReturnValue = false;

    /**
     * @var array
     */
    protected $executedListeners = [];

    /**
     * @var array
     */
    protected $stoppedListeners = [];

    /**
     * @var mixed
     */
    protected $eventName;

    public function __construct($eventName)
    {
        $this->eventName = $eventName;
    }

    public function getEventName()
    {
        return $this->eventName;
    }

    public function getStoppedListeners()
    {
        return $this->stoppedListeners;
    }

    public function addStoppedListener($listener)
    {
        $this->stoppedListeners[] = $listener;
    }

    public function getExecutedListeners()
    {
        return $this->executedListeners;
    }

    public function addExecutedListener($listener)
    {
        $this->executedListeners[] = $listener;
    }

    public function isStopped()
    {
        return $this->stopped;
    }

    public function setStopped($value)
    {
        $this->stopped = $value;
    }

    public function getStopValue()
    {
        return $this->stopValue;
    }

    /**
     * Sets the value which stopped the dispatchment chain.
     *
     * @param mixed $stopValue
     * @return void
     */
    public function setStopValue($stopValue)
    {
        $this->stopValue = $stopValue;
    }

    public function ignoreReturnValue($switch)
    {
        $this->ignoreReturnValue = $switch;
    }

    public function isReturnValueIgnored()
    {
        return $this->ignoreReturnValue;
    }

    public function stop()
    {
        $this->stopped = true;
    }
}
