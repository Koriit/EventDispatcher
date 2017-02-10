<?php

namespace EventDispatcher\Test\IntegrationTests;

use DI\ContainerBuilder;
use EventDispatcher\EventDispatcher;
use EventDispatcher\EventContextInterface;
use EventDispatcher\Test\Fixtures\FakeClass;

class DispatchTest extends \PHPUnit_Framework_TestCase
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
    public function should_not_fail_without_listeners()
    {
        $eventName = 'mock';
        $context = $this->dispatcher->dispatch($eventName);

        $this->assertInstanceOf(EventContextInterface::class, $context);
    }

    /**
     * @test
     */
    public function should_execute_listeners_on_dispatch()
    {
        $eventName = 'mock';
        $listener = function () {
            echo 'Mock';
        };
        $listener2 = function () {
            echo 'Output';
        };
        $this->dispatcher->addListener($eventName, $listener);
        $this->dispatcher->addListener($eventName, $listener2);

        $this->expectOutputString('MockOutput');
        $this->dispatcher->dispatch($eventName);
    }

    /**
     * @test
     */
    public function should_inject_event_name()
    {
        $eventName = 'mock';
        $test = $this;
        $listener = function ($eventName) use ($test) {
            $test->assertEquals('mock', $eventName);
        };
        $this->dispatcher->addListener($eventName, $listener);
        $this->dispatcher->dispatch($eventName);
    }

    /**
     * @test
     */
    public function should_inject_self()
    {
        $eventName = 'mock';
        $test = $this;
        $dispatcher = $this->dispatcher;
        $listener = function ($eventDispatcher) use ($test, $dispatcher) {
            $test->assertEquals($dispatcher, $eventDispatcher);
        };
        $this->dispatcher->addListener($eventName, $listener);
        $this->dispatcher->dispatch($eventName);
    }

    /**
     * @test
     */
    public function should_inject_event_context()
    {
        $eventName = 'mock';
        $test = $this;
        $listener = function ($eventContext) use ($test) {
            $test->assertInstanceOf(EventContextInterface::class, $eventContext);
        };
        $this->dispatcher->addListener($eventName, $listener);
        $this->dispatcher->dispatch($eventName);
    }

    /**
     * @test
     */
    public function should_inject_dependencies()
    {
        $eventName = 'mock';
        $test = $this;
        $listener = function (FakeClass $dependency) use ($test) {
            $test->assertInstanceOf(FakeClass::class, $dependency);
        };
        $this->dispatcher->addListener($eventName, $listener);
        $this->dispatcher->dispatch($eventName);
    }

    /**
     * @test
     */
    public function should_inject_parameters()
    {
        $eventName = 'mock';
        $test = $this;
        $listener = function ($param) use ($test) {
            $test->assertEquals('mock', $param);
        };
        $this->dispatcher->addListener($eventName, $listener);
        $this->dispatcher->dispatch($eventName, ['param' => 'mock']);
    }
}
