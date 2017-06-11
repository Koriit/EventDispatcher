<?php

/**
 *  @copyright 2017 Aleksander Stelmaczonek <al.stelmaczonek@gmail.com>
 *  @license MIT License, see license file distributed with this source code
 */

namespace Koriit\EventDispatcher;

use DI\InvokerInterface;
use Koriit\EventDispatcher\Exceptions\InvalidPriority;

/**
 * @author Aleksander Stelmaczonek <al.stelmaczonek@gmail.com>
 */
class EventDispatcher implements EventDispatcherInterface
{
    protected $listeners = [];

    /**
     * @var InvokerInterface
     */
    protected $invoker;

    /**
     * Indicates whether listeners array can be assumed to be sorted.
     *
     * @var bool
     */
    protected $sorted = true;

    public function __construct(InvokerInterface $invoker)
    {
        $this->invoker = $invoker;
    }

    public function dispatch($eventName, $parameters = [])
    {
        $eventContext = new EventContext($eventName);

        if (!isset($this->listeners[$eventName])) {
            return $eventContext;
        }

        if (!$this->sorted) {
            $this->sortListenersByPriority();
        }

        $parameters['eventName'] = $eventName;
        $parameters['eventContext'] = $eventContext;
        $parameters['eventDispatcher'] = $this;

        foreach ($this->listeners[$eventName] as $listeners) {
            foreach ($listeners as $listener) {
                if ($eventContext->isStopped()) {
                    $eventContext->addStoppedListener($listener);
                } else {
                    if ($result = $this->invoker->call($listener, $parameters)) {
                        $eventContext->setStopped(true);
                        $eventContext->setStopValue($result);
                    }
                    $eventContext->addExecutedListener($listener);
                }
            }
        }

        return $eventContext;
    }

    public function addListener($eventName, $listener, $priority = 0)
    {
        if (!is_int($priority) || $priority < 0) {
            throw new InvalidPriority('Expected non-negative integer priority. Provided: ' . $priority);
        }

        $this->listeners[$eventName][$priority][] = $listener;
        $this->sorted = false;
    }

    public function addListeners($listeners)
    {
        foreach ($listeners as $eventName => $listenersByPriority) {
            foreach ($listenersByPriority as $priority => $newListeners) {
                if (!is_int($priority) || $priority < 0) {
                    throw new InvalidPriority('Expected non-negative integer priority. Provided: ' . $priority);
                }

                foreach ($newListeners as $listener) {
                    $this->listeners[$eventName][$priority][] = $listener;
                }
            }
        }

        $this->sorted = false;
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
                if (empty($this->listeners[$eventName][$priority])) {
                    unset($this->listeners[$eventName][$priority]);
                }
            }
        }

        if (empty($this->listeners[$eventName])) {
            unset($this->listeners[$eventName]);
        }
    }

    public function hasListeners($eventName = null)
    {
        return $eventName !== null ? !empty($this->listeners[$eventName]) : !empty($this->listeners);
    }

    protected function sortListenersByPriority() {
        foreach (array_keys($this->listeners) as $eventName) {
            ksort($this->listeners[$eventName]);
        }
        $this->sorted = true;
    }
}
