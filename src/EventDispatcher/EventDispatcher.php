<?php

/**
 * @copyright 2018 Aleksander Stelmaczonek <al.stelmaczonek@gmail.com>
 * @license   MIT License, see license file distributed with this source code
 */

namespace Koriit\EventDispatcher;

use Invoker\Exception\InvocationException;
use Invoker\Exception\NotCallableException;
use Invoker\Exception\NotEnoughParametersException;
use Invoker\InvokerInterface;
use Koriit\EventDispatcher\Exceptions\InvalidPriority;
use Koriit\EventDispatcher\Exceptions\OverriddenParameter;

class EventDispatcher implements EventDispatcherInterface
{
    /**
     * @var array
     */
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
    protected $listenersSorted = true;

    public function __construct(InvokerInterface $invoker)
    {
        $this->invoker = $invoker;
    }

    public function dispatch($eventName, $parameters = [])
    {
        $eventContext = new EventContext($eventName);
        $this->injectDispatcherParameters($eventContext, $parameters);

        if (isset($this->listeners[$eventName])) {
            $this->sortListenersByPriority();

            foreach ($this->listeners[$eventName] as $listenersByPriority) {
                foreach ($listenersByPriority as $listener) {
                    $this->invokeListener($eventContext, $listener, $parameters);
                }
            }
        }

        return $eventContext;
    }

    public function addListener($eventName, $listener, $priority = 0)
    {
        $this->validatePriority($priority);

        $this->listeners[$eventName][$priority][] = $listener;
        $this->listenersSorted = false;
    }

    public function addListeners($listeners)
    {
        foreach ($listeners as $eventName => $eventListeners) {
            foreach ($eventListeners as $priority => $listenersByPriority) {
                $this->validatePriority($priority);

                foreach ($listenersByPriority as $listener) {
                    $this->listeners[$eventName][$priority][] = $listener;
                }
            }
        }

        $this->listenersSorted = false;
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
            $key = array_search($listener, $listeners, true);
            if ($key !== false) {
                unset($this->listeners[$eventName][$priority][$key]);
                $this->removeEmptyItem($this->listeners[$eventName], $priority);
            }
        }

        $this->removeEmptyItem($this->listeners, $eventName);
    }

    public function hasListeners($eventName = null)
    {
        return $eventName !== null ? !empty($this->listeners[$eventName]) : !empty($this->listeners);
    }

    /**
     * Sorts internal listeners array by priority.
     *
     * @return void
     */
    protected function sortListenersByPriority()
    {
        if ($this->listenersSorted) {
            return;
        }

        foreach (array_keys($this->listeners) as $eventName) {
            ksort($this->listeners[$eventName]);
        }

        $this->listenersSorted = true;
    }

    /**
     * @param EventContext $eventContext
     * @param callable     $listener
     * @param array        $parameters
     *
     * @throws InvocationException
     * @throws NotCallableException
     * @throws NotEnoughParametersException
     *
     * @return void
     */
    protected function invokeListener($eventContext, $listener, $parameters = [])
    {
        if ($eventContext->isStopped()) {
            $eventContext->addStoppedListener($listener);

            return;
        }

        $eventContext->ignoreReturnValue(false);
        $returnValue = $this->invoker->call($listener, $parameters);

        if ($eventContext->isStopped()) {
            $eventContext->setStopValue(true);
        } elseif ($this->isStopValue($eventContext, $returnValue)) {
            $eventContext->setStopped(true);
            $eventContext->setStopValue($returnValue);
        }

        $eventContext->addExecutedListener($listener);
    }

    /**
     * Injects predefined dispatcher objects into parameters array.
     *
     * @param EventContext $eventContext
     * @param array        $parameters
     *
     * @throws OverriddenParameter
     *
     * @return void
     */
    protected function injectDispatcherParameters($eventContext, &$parameters)
    {
        $intersect = \array_intersect_key(\array_flip(['eventName', 'eventContext', 'eventDispatcher']), $parameters);
        if (!empty($intersect)) {
            throw new OverriddenParameter('Parameters array cannot contain: ' . implode(', ', \array_flip($intersect)));
        }

        $parameters['eventName'] = $eventContext->getEventName();
        $parameters['eventContext'] = $eventContext;
        $parameters['eventDispatcher'] = $this;
    }

    /**
     * @param mixed $priority
     *
     * @throws InvalidPriority
     *
     * @return void
     */
    protected function validatePriority($priority)
    {
        if (!is_int($priority) || $priority < 0) {
            throw new InvalidPriority('Expected non-negative integer priority. Provided: ' . $priority);
        }
    }

    /**
     * @param EventContext $eventContext
     * @param mixed        $returnValue
     *
     * @return bool
     */
    protected function isStopValue($eventContext, $returnValue)
    {
        return !$eventContext->isReturnValueIgnored() && $returnValue;
    }

    /**
     * Removes an item by key from the array if it is considered to be empty.
     *
     * @param array $array
     * @param mixed $key
     */
    protected function removeEmptyItem(&$array, $key)
    {
        if (empty($array[$key])) {
            unset($array[$key]);
        }
    }
}
