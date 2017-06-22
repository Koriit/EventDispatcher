<?php

namespace Koriit\EventDispatcher\Test\IntegrationTests;

use DI\ContainerBuilder;
use Koriit\EventDispatcher\EventContextInterface;
use Koriit\EventDispatcher\EventDispatcher;
use Koriit\EventDispatcher\EventDispatcherInterface;
use Koriit\EventDispatcher\Exceptions\OverriddenParameter;
use Koriit\EventDispatcher\Test\Fixtures\FakeClass;

class DispatchTest extends \PHPUnit_Framework_TestCase
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
     * Event should be dispatchable even if no listeners subscribed to it.
     *
     * @test
     */
    public function shouldNotFailWithoutListeners()
    {
        $eventName = 'mock';
        $context = $this->dispatcher->dispatch($eventName);

        $this->assertInstanceOf(EventContextInterface::class, $context);
    }

    /**
     * @test
     */
    public function shouldAllowRepeatedDispatchments()
    {
        $eventName = 'mock';
        $listener = function () {
            echo 'MockOutput';
        };
        $this->dispatcher->addListener($eventName, $listener);

        $this->expectOutputString('MockOutputMockOutput');

        $this->dispatcher->dispatch($eventName);
        $this->dispatcher->dispatch($eventName);
    }

    /**
     * @test
     */
    public function shouldExecuteListenersOnDispatch()
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
    public function shouldExecuteOnlyEventListeners()
    {
        $eventName = 'mock';
        $listener = function () {
            echo 'Mock';
        };
        $this->dispatcher->addListener($eventName, $listener);

        $eventName2 = 'mock2';
        $listener2 = function () {
            echo 'Output';
        };
        $this->dispatcher->addListener($eventName2, $listener2);

        $this->expectOutputString('Mock');
        $this->dispatcher->dispatch($eventName);
    }

    /**
     * @test
     */
    public function shouldExecuteListenersByPriority()
    {
        $eventName = 'mock';
        $listener = function () {
            echo 'Mock';
        };
        $listener2 = function () {
            echo 'Output';
        };

        $this->dispatcher->addListener($eventName, $listener2, 2);
        $this->dispatcher->addListener($eventName, $listener, 1);

        $this->expectOutputString('MockOutput');
        $this->dispatcher->dispatch($eventName);
    }

    /**
     * @test
     */
    public function shouldExecuteListenersByPriorityWithBulk()
    {
        $eventName = 'mock';

        $listeners = [
            $eventName => [
                1 => [
                    function () {
                        echo 'Output';
                    },
                ],
                0 => [
                    function () {
                        echo 'Mock';
                    },
                ],
            ],
        ];

        $this->dispatcher->addListeners($listeners);

        $this->expectOutputString('MockOutput');
        $this->dispatcher->dispatch($eventName);
    }

    /**
     * @test
     */
    public function shouldInjectEventName()
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
    public function shouldInjectSelf()
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
    public function shouldInjectEventContext()
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
    public function shouldInjectDependencies()
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
    public function shouldInjectParameters()
    {
        $eventName = 'mock';
        $mockParam = 'mockParam';
        $test = $this;

        $listener = function ($param) use ($test, $mockParam) {
            $test->assertEquals($mockParam, $param);
        };

        $this->dispatcher->addListener($eventName, $listener);
        $this->dispatcher->dispatch($eventName, ['param' => $mockParam]);
    }

    /**
     * @test
     * @dataProvider overriddenParametersProvider
     *
     * @param string $param
     * @param mixed  $value
     */
    public function shouldThrowWhenParametersOverriddenWithoutListeners($param, $value)
    {
        $this->setExpectedException(OverriddenParameter::class);

        $this->dispatcher->dispatch('mockEvent', [$param => $value]);
    }

    /**
     * @test
     * @dataProvider overriddenParametersProvider
     *
     * @param string $param
     */
    public function shouldThrowWhenParametersOverridden($param)
    {
        $this->setExpectedException(OverriddenParameter::class);

        $eventName = 'mock';
        $listener = function () {
        };

        $this->dispatcher->addListener($eventName, $listener);
        $this->dispatcher->dispatch($eventName, [$param => $param]);
    }

    public function overriddenParametersProvider()
    {
        return [
            ['eventName', 'non-null'],
            ['eventName', null],
            ['eventContext', 'non-null'],
            ['eventContext', null],
            ['eventDispatcher', 'non-null'],
            ['eventDispatcher', null],
        ];
    }
}
