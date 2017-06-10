<?php

/**
 *  @copyright 2017 Aleksander Stelmaczonek <al.stelmaczonek@gmail.com>
 *  @license MIT License, see license file distributed with this source code
 */

namespace Koriit\EventDispatcher\Exceptions;


use Throwable;

class InvalidPriority extends \LogicException
{
    public function __construct($message)
    {
        parent::__construct($message);
    }
}