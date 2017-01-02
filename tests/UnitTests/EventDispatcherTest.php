<?php
namespace EventDispatcher\Test\UnitTests;

use DI\ContainerBuilder;
use EventDispatcher\EventDispatcher;

class EventDispatcherTest extends \PHPUnit_Framework_TestCase
{
    protected static $mockListener;

    public static function setUpBeforeClass()
    {
        self::$mockListener = function(){};
    }

    /**
     * @test
     */
    public function should_allow_adding_listeners()
    {
        $invoker = ContainerBuilder::buildDevContainer();
        $dispatcher = new EventDispatcher($invoker);

        $eventName = "mock";
        $dispatcher->addListener($eventName, self::$mockListener);

        $this->assertTrue($dispatcher->hasListeners());
        $this->assertTrue($dispatcher->hasListeners($eventName));

        $listneres = $dispatcher->getListeners($eventName);
        $this->assertFalse(empty($listneres));
        $this->assertEquals(self::$mockListener, $listneres[0][0]);

        $allListeners = $dispatcher->getAllListeners();
        $this->assertFalse(empty($allListeners));
        $this->assertEquals(self::$mockListener, $allListeners[$eventName][0][0]);
    }

    /**
     * @test
     * @depends should_allow_adding_listeners
     */
    public function should_allow_removing_listeners()
    {
        $invoker = ContainerBuilder::buildDevContainer();
        $dispatcher = new EventDispatcher($invoker);

        $eventName = "mock";
        $dispatcher->addListener($eventName, self::$mockListener);
        $dispatcher->removeListener($eventName, self::$mockListener);

        $this->assertFalse($dispatcher->hasListeners());
        $this->assertFalse($dispatcher->hasListeners($eventName));

        $listneres = $dispatcher->getListeners($eventName);
        $this->assertTrue(empty($listneres));

        $allListeners = $dispatcher->getAllListeners();
        $this->assertTrue(empty($allListeners));
    }

    /**
     * @test
     * @dataProvider bulkListenersProvider
     */
    public function should_allow_adding_bulk_listeners($manualListeners, $bulkListeners, $expected)
    {
        $invoker = ContainerBuilder::buildDevContainer();
        $dispatcher = new EventDispatcher($invoker);

        foreach($manualListeners as $eventName => $byPriority)
        {
            foreach($byPriority as $priority => $listeners)
            {
                foreach($listeners as $listener)
                {
                    $dispatcher->addListener($eventName, $listener, $priority);
                }
            }
        }
        $dispatcher->addListeners($bulkListeners);

        $this->assertEquals($expected, $dispatcher->getAllListeners());
    }

    public function bulkListenersProvider()
    {
        return [
          "onlyBulk" => [
              [],
              [
                "mockEvent" => [
                    0 => [
                        function(){}
                    ]
                ]
              ],
              [
                "mockEvent" => [
                    0 => [
                        function(){}
                    ]
                ]
              ]
          ],
          "onlyManual" => [
              [
                "mockEvent" => [
                    0 => [
                        function(){}
                    ]
                ]
              ],
              [],
              [
                "mockEvent" => [
                    0 => [
                        function(){}
                    ]
                ]
              ]
          ],
          "sameBulkAndManual" => [
              [
                "mockEvent" => [
                    0 => [
                        function(){}
                    ]
                ]
              ],
              [
                "mockEvent" => [
                    0 => [
                        function(){}
                    ]
                ]
              ],
              [
                "mockEvent" => [
                    0 => [
                        function(){},
                        function(){},
                    ]
                ]
              ],
          ],
          "sameBulkAndManualDiffPriority" => [
              [
                "mockEvent" => [
                    0 => [
                        function(){}
                    ]
                ]
              ],
              [
                "mockEvent" => [
                    1 => [
                        function(){}
                    ]
                ]
              ],
              [
                "mockEvent" => [
                    0 => [
                        function(){}
                    ],
                    1 => [
                        function(){}
                    ]
                ]
              ],
          ],
          "diffBulkAndManual" => [
              [
                "mockEvent2" => [
                    0 => [
                        function(){}
                    ]
                ]
              ],
              [
                "mockEvent" => [
                    0 => [
                        function(){}
                    ]
                ]
              ],
              [
                "mockEvent" => [
                    0 => [
                        function(){}
                    ]
                ],
                "mockEvent2" => [
                    0 => [
                        function(){}
                    ]
                ],
              ],
          ],
        ];
    }
}
