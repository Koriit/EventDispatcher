<?php

/**
 *  @copyright 2016 Aleksander Stelmaczonek <al.stelmaczonek@gmail.com>
 *  @license MIT License, see license file distributed with this source code
 */

namespace EventDispatcher;

/**
 * @author Aleksander Stelmaczonek <al.stelmaczonek@gmail.com>
 */
class EventContext implements EventContextInterface
{
    protected $stopped = false;

    protected $stopValue = null;

    protected $executedListeners = [];

    protected $stoppedListeners = [];

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

    public function setStopValue($stopValue)
    {
        $this->stopValue = $stopValue;
    }
}
