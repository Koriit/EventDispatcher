<?php

/**
 *  @copyright 2017 Aleksander Stelmaczonek <al.stelmaczonek@gmail.com>
 *  @license MIT License, see license file distributed with this source code
 */

namespace Koriit\EventDispatcher;

/**
 * Event context stores information about the event being dispatched and the dispatchment itself.
 *
 * @author Aleksander Stelmaczonek <al.stelmaczonek@gmail.com>
 */
interface EventContextInterface
{
    /**
     * Returns the name of event being dispatched.
     *
     * @return mixed
     */
    public function getEventName();

    /**
     * Returns array of listeners which were skipped due to dispatchment chain being stopped.
     *
     * @return array
     */
    public function getStoppedListeners();

    /**
     * Returns array of listeners which were successfully invoked.
     *
     * @return array
     */
    public function getExecutedListeners();

    /**
     * Returns the value which was returned from listener and lead to stopping the dispatchment chain.
     *
     * @return mixed
     */
    public function getStopValue();

    /**
     * Returns whether dispatchment chain has been stopped.
     *
     * @return bool
     */
    public function isStopped();
}
