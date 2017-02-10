<?php

namespace EventDispatcher\Test\IntegrationTests;

use DI\ContainerBuilder;
use EventDispatcher\EventDispatcher;
use EventDispatcher\EventContextInterface;

class ReturnContextTest extends \PHPUnit_Framework_TestCase
{
    protected $dispatcher;

    public function setUp()
    {
        $invoker = ContainerBuilder::buildDevContainer();
        $this->dispatcher = new EventDispatcher($invoker);
    }

    /**
     * @test
     */
    public function should_return_context()
    {
        $listener = function () {
        };
        $eventName = 'mock';
        $this->dispatcher->addListener($eventName, $listener);

        $context = $this->dispatcher->dispatch($eventName);

        $this->assertInstanceOf(EventContextInterface::class, $context);
    }

    /**
     * @test
     */
    public function should_execute_all_listeners()
    {
        $listener1 = function () {
        };
        $listener2 = function () {
        };

        $eventName = 'mock';
        $this->dispatcher->addListener($eventName, $listener1);
        $this->dispatcher->addListener($eventName, $listener2);

        $context = $this->dispatcher->dispatch($eventName);

        $this->assertFalse($context->isStopped());

        $executedListeners = $context->getExecutedListeners();
        $this->assertContains($listener1, $executedListeners);
        $this->assertContains($listener2, $executedListeners);
    }

    /**
     * @test
     */
    public function should_stop_after_first_listener()
    {
        $listener1 = function () {
            return true;
        };
        $listener2 = function () {
        };

        $eventName = 'mock';
        $this->dispatcher->addListener($eventName, $listener1);
        $this->dispatcher->addListener($eventName, $listener2);

        $context = $this->dispatcher->dispatch($eventName);

        $this->assertTrue($context->isStopped());

        $this->assertContains($listener1, $context->getExecutedListeners());
        $this->assertContains($listener2, $context->getStoppedListeners());
    }
}
