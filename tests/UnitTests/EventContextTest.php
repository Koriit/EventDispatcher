<?php

namespace Koriit\EventDispatcher\Test\UnitTests;

use Koriit\EventDispatcher\EventContext;
use Koriit\EventDispatcher\EventContextInterface;

class EventContextTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EventContextInterface
     */
    protected $context;

    /**
     * @var mixed
     */
    protected $eventName;

    /**
     * @var callable
     */
    protected $mockListener;

    public function setUp()
    {
        $this->eventName = 'mock';
        $this->mockListener = function () {
        };
        $this->context = new EventContext($this->eventName);
    }

    /**
     * @test
     */
    public function should_return_proper_name()
    {
        $this->assertEquals($this->eventName, $this->context->getEventName());
    }

    /**
     * @test
     */
    public function should_allow_adding_executed_listeners()
    {
        $this->context->addExecutedListener($this->mockListener);

        $this->assertTrue(in_array($this->mockListener, $this->context->getExecutedListeners(), true));
    }

    /**
     * @test
     */
    public function should_allow_adding_stopped_listeners()
    {
        $this->context->addStoppedListener($this->mockListener);

        $this->assertTrue(in_array($this->mockListener, $this->context->getStoppedListeners(), true));
    }

    /**
     * @test
     */
    public function should_not_be_stopped_by_default()
    {
        $this->assertFalse($this->context->isStopped());
    }

    /**
     * @test
     */
    public function should_allow_stopping_event()
    {
        $this->context->setStopped(true);

        $this->assertTrue($this->context->isStopped());

        return $this->context;
    }

    /**
     * @test
     * @depends should_allow_stopping_event
     *
     * @param EventContext $context
     */
    public function should_allow_resuming_event($context)
    {
        $context->setStopped(false);

        $this->assertFalse($context->isStopped());
    }

    public function should_allow_ignoring_return_value()
    {
        $this->context->ignoreReturnValue(true);
        $this->assertTrue($this->context->isReturnValueIgnored());

        $this->context->ignoreReturnValue(false);
        $this->assertFalse($this->context->isReturnValueIgnored());
    }
}
