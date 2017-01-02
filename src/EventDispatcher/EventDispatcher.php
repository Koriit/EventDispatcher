<?php
namespace EventDispatcher;

use EventDispatcher\EventDispatcherInterface;
use DI\InvokerInterface;

class EventDispatcher implements EventDispatcherInterface
{
    protected $listeners = [];

    protected $invoker;

    public function __construct(InvokerInterface $invoker)
    {
        $this->invoker = $invoker;
    }

    public function dispatch($eventName, $parameters = [])
    {
        $eventContext = new EventContext($eventName);

        $parameters["eventName"] = $eventName;
        $parameters["eventContext"] = $eventContext;
        $parameters["eventDispatcher"] = $this;

        foreach($this->listeners[$eventName] as $listeners)
        {
            foreach($listeners as $listener)
            {
                if($eventContext->isStopped())
                {
                    $eventContext->addStoppedListener($listener);
                }
                else
                {
                    if($this->invoker->call($listener, $parameters))
                    {
                        $eventContext->setStopped(true);
                    }
                    $eventContext->addExecutedListener($listener);
                }
            }
        }

        return $eventContext;
    }

    public function addListener($eventName, $listener, $priority = 0)
    {
        $this->listeners[$eventName][$priority][] = $listener;
    }

    public function addListeners($listeners)
    {
        if(empty($this->listeners))
        {
            $this->listeners = $listeners;
        }
        else
        {
            foreach($listeners as $eventName => $byPriority)
            {
                foreach($byPriority as $priority => $newListeners)
                {
                    foreach($newListeners as $listener)
                    {
                        $this->listeners[$eventName][$priority][] = $listener;
                    }
                }
            }
        }
    }

    public function getListeners($eventName)
    {
        return isset($this->listeners[$eventName]) ? $this->listeners[$eventName] : [];
    }

    public function getAllListeners()
    {
        return $this->listeners;
    }

    public function removeListener($eventName, $listener)
    {
        if (!isset($this->listeners[$eventName])) {
            return;
        }

        foreach ($this->listeners[$eventName] as $priority => $listeners) {
            if (false !== ($key = array_search($listener, $listeners, true))) {
                unset($this->listeners[$eventName][$priority][$key]);
                if(empty($this->listeners[$eventName][$priority]))
                {
                    unset($this->listeners[$eventName][$priority]);
                }
            }
        }

        if(empty($this->listeners[$eventName]))
        {
            unset($this->listeners[$eventName]);
        }
    }

    public function hasListeners($eventName = NULL)
    {
        return $eventName !== NULL ? !empty($this->listeners[$eventName]) : !empty($this->listeners);
    }

}
