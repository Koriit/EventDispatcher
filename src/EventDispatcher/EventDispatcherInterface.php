<?php

/**
 *  @copyright 2016 Aleksander Stelmaczonek <al.stelmaczonek@gmail.com>
 *  @license MIT License, see license file distributed with this source code.
 */

namespace EventDispatcher;


/**
 *
 *
 * @author Aleksander Stelmaczonek <al.stelmaczonek@gmail.com>
 */
interface EventDispatcherInterface
{
    /**
     * @param mixed $eventName
     * @param array $parameters
     * @return EventContext
     */
    public function dispatch($eventName, $parameters = []);

    /**
     * @param mixed $eventName
     * @param mixed $listener
     * @param number $priority
     */
    public function addListener($eventName, $listener, $priority = 0);

    /**
     * @param array $listeners
     */
    public function addListeners($listeners);

    /**
     * @param mixed $eventName
     * @param mixed $listener
     */
    public function removeListener($eventName, $listener);

    /**
     * @param mixed $eventName
     * @return array
     */
    public function getListeners($eventName);

    /**
     * @return array
     */
    public function getAllListeners();

    /**
     * Checks whether there are any listeners registered for given event name.
     *
     * @param mixed $eventName
     * @return bool
     */
    public function hasListeners($eventName);
}
