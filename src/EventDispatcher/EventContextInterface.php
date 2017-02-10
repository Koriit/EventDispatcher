<?php

namespace EventDispatcher;

interface EventContextInterface
{
    public function getEventName();

    public function getStoppedListeners();

    public function getExecutedListeners();

    public function isStopped();
}
