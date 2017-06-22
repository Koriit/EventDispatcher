<?php

namespace Koriit\EventDispatcher\Test\IntegrationTests;

use DI\ContainerBuilder;
use Koriit\EventDispatcher\EventContextInterface;
use Koriit\EventDispatcher\EventDispatcher;
use Koriit\EventDispatcher\EventDispatcherInterface;

class ReturnContextTest extends \PHPUnit_Framework_TestCase
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
    public function shouldReturnContext()
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
    public function shouldContainStopValueInContext()
    {
        $listener = function () {
            return 'StopValue';
        };
        $eventName = 'mock';
        $this->dispatcher->addListener($eventName, $listener);

        $context = $this->dispatcher->dispatch($eventName);

        $this->assertEquals('StopValue', $context->getStopValue());
    }

    /**
     * @test
     */
    public function shouldExecuteAllListenersIfNoneReturnsTruth()
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

        $this->assertContains($listener1, $context->getExecutedListeners());
        $this->assertContains($listener2, $context->getExecutedListeners());
        $this->assertEmpty($context->getStoppedListeners());
    }

    /**
     * @test
     */
    public function shouldStopAfterFirstListenerReturningTruth()
    {
        $listener1 = function () {
        };
        $listener2 = function () {
            return true;
        };
        $listener3 = function () {
        };

        $eventName = 'mock';
        $this->dispatcher->addListener($eventName, $listener1);
        $this->dispatcher->addListener($eventName, $listener2);
        $this->dispatcher->addListener($eventName, $listener3);

        $context = $this->dispatcher->dispatch($eventName);

        $this->assertTrue($context->isStopped());

        $this->assertContains($listener1, $context->getExecutedListeners());
        $this->assertContains($listener2, $context->getExecutedListeners());
        $this->assertContains($listener3, $context->getStoppedListeners());
    }

    /**
     * @test
     */
    public function shouldStopAfterContextStop()
    {
        $listener1 = function () {
        };
        $listener2 = function ($eventContext) {
            $eventContext->stop();
        };
        $listener3 = function () {
        };

        $eventName = 'mock';
        $this->dispatcher->addListener($eventName, $listener1);
        $this->dispatcher->addListener($eventName, $listener2);
        $this->dispatcher->addListener($eventName, $listener3);

        $context = $this->dispatcher->dispatch($eventName);

        $this->assertTrue($context->isStopped());

        $this->assertContains($listener1, $context->getExecutedListeners());
        $this->assertContains($listener2, $context->getExecutedListeners());
        $this->assertContains($listener3, $context->getStoppedListeners());
    }

    /**
     * @test
     * @depends shouldStopAfterContextStop
     */
    public function shouldSetStopValueToTrueAfterContextStop()
    {
        $listener1 = function ($eventContext) {
            $eventContext->stop();
        };

        $eventName = 'mock';
        $this->dispatcher->addListener($eventName, $listener1);
        $context = $this->dispatcher->dispatch($eventName);

        $this->assertSame(true, $context->getStopValue());
    }

    /**
     * @test
     * @depends shouldStopAfterContextStop
     */
    public function shouldSetStopValueToTrueAfterContextStopEvenWithReturnValue()
    {
        $listener1 = function ($eventContext) {
            $eventContext->stop();

            return 'TruthValue';
        };

        $eventName = 'mock';
        $this->dispatcher->addListener($eventName, $listener1);
        $context = $this->dispatcher->dispatch($eventName);

        $this->assertSame(true, $context->getStopValue());
    }
}
