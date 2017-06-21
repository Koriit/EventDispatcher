<?php

namespace Koriit\EventDispatcher\Test\UnitTests;

use DI\ContainerBuilder;
use Koriit\EventDispatcher\EventDispatcher;
use Koriit\EventDispatcher\Exceptions\InvalidPriority;

class EventDispatcherTest extends \PHPUnit_Framework_TestCase
{
    protected static $mockListener;

    /**
     * @var EventDispatcher
     */
    protected $dispatcher;

    public static function setUpBeforeClass()
    {
        self::$mockListener = function () {
        };
    }

    public function setUp()
    {
        $invoker = ContainerBuilder::buildDevContainer();
        $this->dispatcher = new EventDispatcher($invoker);
    }

    /**
     * @test
     */
    public function should_allow_adding_listeners()
    {
        $eventName = 'mock';
        $this->dispatcher->addListener($eventName, self::$mockListener);

        $this->assertTrue($this->dispatcher->hasListeners());
        $this->assertTrue($this->dispatcher->hasListeners($eventName));

        $listneres = $this->dispatcher->getListeners($eventName);
        $this->assertFalse(empty($listneres));
        $this->assertEquals(self::$mockListener, $listneres[0][0]);

        $allListeners = $this->dispatcher->getAllListeners();
        $this->assertFalse(empty($allListeners));
        $this->assertEquals(self::$mockListener, $allListeners[$eventName][0][0]);
    }

    /**
     * @test
     * @depends should_allow_adding_listeners
     */
    public function should_allow_removing_listeners()
    {
        $eventName = 'mock';
        $this->dispatcher->addListener($eventName, self::$mockListener);
        $this->dispatcher->removeListener($eventName, self::$mockListener);

        $this->assertFalse($this->dispatcher->hasListeners());
        $this->assertFalse($this->dispatcher->hasListeners($eventName));

        $listneres = $this->dispatcher->getListeners($eventName);
        $this->assertTrue(empty($listneres));

        $allListeners = $this->dispatcher->getAllListeners();
        $this->assertTrue(empty($allListeners));
    }

    /**
     * @test
     */
    public function should_allow_removing_listeners_for_nonexistent_events()
    {
        $eventName = 'mock';
        $this->assertEmpty($this->dispatcher->getAllListeners());
        $this->dispatcher->removeListener($eventName, self::$mockListener);
    }

    /**
     * @test
     */
    public function should_not_allow_negative_priority()
    {
        $this->setExpectedException(InvalidPriority::class);

        $this->dispatcher->addListener('test', self::$mockListener, -1);
    }

    /**
     * @test
     */
    public function should_allow_only_integer_priority()
    {
        $this->setExpectedException(InvalidPriority::class);

        $this->dispatcher->addListener('test', self::$mockListener, 'priority');
    }

    /**
     * @test
     */
    public function should_not_allow_negative_priority_with_bulk()
    {
        $this->setExpectedException(InvalidPriority::class);

        $listeners = [
            'mockEvent' => [
                -1 => [
                    function () {
                    },
                ],
            ],
        ];

        $this->dispatcher->addListeners($listeners);
    }

    /**
     * @test
     */
    public function should_allow_only_integer_priority_with_bulk()
    {
        $this->setExpectedException(InvalidPriority::class);

        $listeners = [
            'mockEvent' => [
                'priority' => [
                    function () {
                    },
                ],
            ],
        ];

        $this->dispatcher->addListeners($listeners);
    }

    /**
     * @test
     * @dataProvider bulkListenersProvider
     */
    public function should_allow_adding_bulk_listeners($manualListeners, $bulkListeners, $expected)
    {
        foreach ($manualListeners as $eventName => $byPriority) {
            foreach ($byPriority as $priority => $listeners) {
                foreach ($listeners as $listener) {
                    $this->dispatcher->addListener($eventName, $listener, $priority);
                }
            }
        }
        $this->dispatcher->addListeners($bulkListeners);

        $this->assertEquals($expected, $this->dispatcher->getAllListeners());
    }

    public function bulkListenersProvider()
    {
        return [
          'onlyBulk' => [
              [],
              [
                'mockEvent' => [
                    0 => [
                        function () {
                        },
                    ],
                ],
              ],
              [
                'mockEvent' => [
                    0 => [
                        function () {
                        },
                    ],
                ],
              ],
          ],
          'onlyManual' => [
              [
                'mockEvent' => [
                    0 => [
                        function () {
                        },
                    ],
                ],
              ],
              [],
              [
                'mockEvent' => [
                    0 => [
                        function () {
                        },
                    ],
                ],
              ],
          ],
          'sameBulkAndManual' => [
              [
                'mockEvent' => [
                    0 => [
                        function () {
                        },
                    ],
                ],
              ],
              [
                'mockEvent' => [
                    0 => [
                        function () {
                        },
                    ],
                ],
              ],
              [
                'mockEvent' => [
                    0 => [
                        function () {
                        },
                        function () {
                        },
                    ],
                ],
              ],
          ],
          'sameBulkAndManualDiffPriority' => [
              [
                'mockEvent' => [
                    0 => [
                        function () {
                        },
                    ],
                ],
              ],
              [
                'mockEvent' => [
                    1 => [
                        function () {
                        },
                    ],
                ],
              ],
              [
                'mockEvent' => [
                    0 => [
                        function () {
                        },
                    ],
                    1 => [
                        function () {
                        },
                    ],
                ],
              ],
          ],
          'diffBulkAndManual' => [
              [
                'mockEvent2' => [
                    0 => [
                        function () {
                        },
                    ],
                ],
              ],
              [
                'mockEvent' => [
                    0 => [
                        function () {
                        },
                    ],
                ],
              ],
              [
                'mockEvent' => [
                    0 => [
                        function () {
                        },
                    ],
                ],
                'mockEvent2' => [
                    0 => [
                        function () {
                        },
                    ],
                ],
              ],
          ],
        ];
    }
}
