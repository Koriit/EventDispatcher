<?php

/**
 *  @copyright 2017 Aleksander Stelmaczonek <al.stelmaczonek@gmail.com>
 *  @license MIT License, see license file distributed with this source code
 */

namespace Koriit\EventDispatcher;

/**
 * Event dispatcher allows you to notify listeners subscribed to an event.
 *
 * @author Aleksander Stelmaczonek <al.stelmaczonek@gmail.com>
 */
interface EventDispatcherInterface
{
    /**
     * Dispatches an event with given name.
     *
     * @param mixed $eventName
     * @param array $parameters
     *
     * @return EventContextInterface
     */
    public function dispatch($eventName, $parameters = []);

    /**
     * Subscribes a listener to given event with specific priority.
     *
     * Listener must be invokable by PHP-DI.
     *
     * The higher the priority value the later the listener will be called.
     * Listeners with the same priority will be called in the order they have been subscribed.
     *
     * @param mixed  $eventName
     * @param mixed  $listener
     * @param integer $priority
     *
     * @return void
     */
    public function addListener($eventName, $listener, $priority = 0);

    /**
     * Subscribes listeners en masse.
     *
     * Listeners array is simple structure of 3 levels.
     * At first level it is associative array where keys are names of registered events.
     * At second level it is indexed array where keys are priority values.
     * At third level it is simple list containing listeners subscribed to given event with given priority.
     *
     * @param array $listeners
     *
     * @return void
     */
    public function addListeners($listeners);

    /**
     * Unsubscribes the listener from given event.
     *
     * Listener is searched with strict comparison.
     *
     * If listener is the last subscribed listener for given event, the event is removed from listeners array.
     *
     * @param mixed $eventName
     * @param mixed $listener
     *
     * @return void
     */
    public function removeListener($eventName, $listener);

    /**
     * Returns array of listeners subscribed to given event.
     *
     * Returns only second and third level of listeners array.
     *
     * @see EventDispatcherInterface::addListeners For description of listeners array
     *
     * @param mixed $eventName
     *
     * @return array
     */
    public function getListeners($eventName);

    /**
     * Returns listeners array.
     *
     * @see EventDispatcherInterface::addListeners For description of listeners array
     *
     * @return array
     */
    public function getAllListeners();

    /**
     * Checks whether there are any listeners registered for given event name.
     *
     * @param mixed $eventName
     *
     * @return bool
     */
    public function hasListeners($eventName);
}
