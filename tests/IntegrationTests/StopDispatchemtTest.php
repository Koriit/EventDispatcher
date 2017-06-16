<?php

namespace Koriit\EventDispatcher\Test\IntegrationTests;


use DI\ContainerBuilder;
use Koriit\EventDispatcher\EventDispatcher;
use Koriit\EventDispatcher\EventDispatcherInterface;

class StopDispatchemtTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    public function setUp()
    {
        $invoker = ContainerBuilder::buildDevContainer();
        $this->dispatcher = new EventDispatcher($invoker);
    }

    /**
     * @test
     */
    public function should_stop_with_truth_return()
    {
        $listener = function () {
            return 'StopTruth';
        };
        $eventName = 'mock';
        $this->dispatcher->addListener($eventName, $listener);

        $context = $this->dispatcher->dispatch($eventName);

        $this->assertTrue($context->isStopped());
    }

    /**
     * @test
     */
    public function should_stop_with_context()
    {
        $listener = function ($eventContext) {
            $eventContext->stop();
        };
        $eventName = 'mock';
        $this->dispatcher->addListener($eventName, $listener);

        $context = $this->dispatcher->dispatch($eventName);

        $this->assertTrue($context->isStopped());
    }

    /**
     * @test
     */
    public function should_allow_ignoring_return_value()
    {
        $listener = function ($eventContext) {
            $eventContext->ignoreReturnValue(true);
            return true;
        };
        $eventName = 'mock';
        $this->dispatcher->addListener($eventName, $listener);

        $context = $this->dispatcher->dispatch($eventName);

        $this->assertFalse($context->isStopped());
    }

    /**
     * @test
     * @depends should_allow_ignoring_return_value
     */
    public function should_allow_context_stop_while_ignoring_return_value()
    {
        $listener = function ($eventContext) {
            $eventContext->ignoreReturnValue(true);
            $eventContext->stop();
            return "TruthValue";
        };
        $eventName = 'mock';
        $this->dispatcher->addListener($eventName, $listener);

        $context = $this->dispatcher->dispatch($eventName);

        $this->assertTrue($context->isStopped());
    }

    /**
     * @test
     * @depends should_allow_ignoring_return_value
     */
    public function should_ignore_return_value_only_for_current_listener()
    {
        $listener = function ($eventContext) {
            $eventContext->ignoreReturnValue(true);
            return "TruthValue";
        };
        $listener2 = function ($eventContext) {
            return "TruthValue";
        };

        $eventName = 'mock';

        $this->dispatcher->addListener($eventName, $listener);
        $this->dispatcher->addListener($eventName, $listener2);

        $context = $this->dispatcher->dispatch($eventName);

        $this->assertTrue($context->isStopped());
    }
}
