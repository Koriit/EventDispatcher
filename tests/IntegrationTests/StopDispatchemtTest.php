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
    public function shouldStopWithTruthReturn()
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
    public function shouldStopWithContext()
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
    public function shouldAllowIgnoringReturnValue()
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
     * @depends shouldAllowIgnoringReturnValue
     */
    public function shouldAllowContextStopWhileIgnoringReturnValue()
    {
        $listener = function ($eventContext) {
            $eventContext->ignoreReturnValue(true);
            $eventContext->stop();

            return 'TruthValue';
        };
        $eventName = 'mock';
        $this->dispatcher->addListener($eventName, $listener);

        $context = $this->dispatcher->dispatch($eventName);

        $this->assertTrue($context->isStopped());
    }

    /**
     * @test
     * @depends shouldAllowIgnoringReturnValue
     */
    public function shouldIgnoreReturnValueOnlyForCurrentListener()
    {
        $listener = function ($eventContext) {
            $eventContext->ignoreReturnValue(true);

            return 'TruthValue';
        };
        $listener2 = function ($eventContext) {
            return 'TruthValue';
        };

        $eventName = 'mock';

        $this->dispatcher->addListener($eventName, $listener);
        $this->dispatcher->addListener($eventName, $listener2);

        $context = $this->dispatcher->dispatch($eventName);

        $this->assertTrue($context->isStopped());
    }
}
