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
    public function shouldReturnProperName()
    {
        $this->assertEquals($this->eventName, $this->context->getEventName());
    }

    /**
     * @test
     */
    public function shouldAllowAddingExecutedListeners()
    {
        $this->context->addExecutedListener($this->mockListener);

        $this->assertTrue(in_array($this->mockListener, $this->context->getExecutedListeners(), true));
    }

    /**
     * @test
     */
    public function shouldAllowAddingStoppedListeners()
    {
        $this->context->addStoppedListener($this->mockListener);

        $this->assertTrue(in_array($this->mockListener, $this->context->getStoppedListeners(), true));
    }

    /**
     * @test
     */
    public function shouldNotBeStoppedByDefault()
    {
        $this->assertFalse($this->context->isStopped());
    }

    /**
     * @test
     */
    public function shouldAllowStoppingEvent()
    {
        $this->context->setStopped(true);

        $this->assertTrue($this->context->isStopped());

        return $this->context;
    }

    /**
     * @test
     * @depends shouldAllowStoppingEvent
     *
     * @param EventContext $context
     */
    public function shouldAllowResumingEvent($context)
    {
        $context->setStopped(false);

        $this->assertFalse($context->isStopped());
    }

    /**
     * @test
     */
    public function shouldAllowIgnoringReturnValue()
    {
        $this->context->ignoreReturnValue(true);
        $this->assertTrue($this->context->isReturnValueIgnored());

        $this->context->ignoreReturnValue(false);
        $this->assertFalse($this->context->isReturnValueIgnored());
    }
}
