EventDispatcher
-----
[![Build Status](https://travis-ci.org/Koriit/EventDispatcher.svg?branch=master)](https://travis-ci.org/Koriit/EventDispatcher)
[![Coverage Status](https://coveralls.io/repos/github/Koriit/EventDispatcher/badge.svg?branch=master)](https://coveralls.io/github/Koriit/EventDispatcher?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Koriit/EventDispatcher/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Koriit/EventDispatcher/?branch=master)
[![StyleCI](https://styleci.io/repos/77447943/shield?branch=master)](https://styleci.io/repos/77447943)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/e22b54a9-b2ce-4b42-8b6b-5f562218da34/mini.png)](https://insight.sensiolabs.com/projects/e22b54a9-b2ce-4b42-8b6b-5f562218da34) 

[![Latest Stable Version](https://poser.pugx.org/koriit/eventdispatcher/v/stable)](https://packagist.org/packages/koriit/eventdispatcher)
[![License](https://poser.pugx.org/koriit/eventdispatcher/license)](https://packagist.org/packages/koriit/eventdispatcher)

Simple event dispatcher based on [PHP-DI](http://php-di.org).

This library **does not** aim to be general purpose library or cover all your possible needs. 
What this library **does** aim to be is perfect choice for those who use PHP-DI and prefer 
to use [PHP Definitions](http://php-di.org/doc/php-definitions.html).

The goal is to create as decoupled code as possible. The code that uses the dispatcher may not know its listeners, and the other way around, the listeners may not even know that they are actually used as listeners!

Install
-------

EventDispatcher is available via composer:

```sh
composer require koriit/eventdispatcher
```

Tested with PHP-DI ^5.4.

Usage
-----
You are encouraged to familiarize yourself with `Koriit\EventDispatcher\EventDispatcherInterface` and 
`Koriit\EventDispatcher\EventContextInterface` as those two interfaces are everything you need to work 
with this library.

Basic example:
```php
// configure and build your container

$dispatcher = new EventDispatcher($container);

$listener = function (LoggerInterface $logger, Request $request) {
    $logger->info($request->getMethod().' '.$request->getPathInfo());
};

$dispatcher->addListener("init", $listener, 10);

$dispatcher->dispatch("init");
```

Naturally since we are using PHP-DI then we would create a definition for 
`Koriit\EventDispatcher\EventDispatcherInterface`.

A listener may be anything that [can be invoked by PHP-DI](http://php-di.org/doc/container.html#call):
```php
// MyClass.php
class MyClass
{
    public function logRequest(LoggerInterface $logger, Request $request)
    {
        $logger->info($request->getMethod().' '.$request->getPathInfo());
    }
}
```
```php
// configure and build your container

$dispatcher = $container->get(EventDispatcherInterface::class);

$dispatcher->addListener(ApplicationLifecycle::INITIALIZING, [MyClass::class, 'logRequest'], 10);

$dispatcher->dispatch(ApplicationLifecycle::INITIALIZING);
```

Even interfaces:
```php
// MyInterface.php
interface MyInterface
{
    public function logRequest(LoggerInterface $logger, Request $request);
}
```
```php
// MyClass.php
class MyClass implements MyInterface
{
    public function logRequest(LoggerInterface $logger, Request $request)
    {
        $logger->info($request->getMethod().' '.$request->getPathInfo());
    }
}
```
```php
// configure and build your container

$dispatcher = $container->get(EventDispatcherInterface::class);

$dispatcher->addListener(ApplicationLifecycle::INITIALIZING, [MyInterface::class, 'logRequest'], 10);

$dispatcher->dispatch(ApplicationLifecycle::INITIALIZING);
```
For above example to work you need to configure a definition for *MyInterface*, of course.

**Warning:**  
Event dispatcher doesn't work well with listeners which implement fluent interface or allow for 
method chaining. For more information, read about stopping dispatchemnt chain below.

Adding listeners
----------------
There are 2 ways to subscribe a listener. In both cases you have to specify name of the event and 
calling priority. The higher the priority value the later the listener will be called. 
Listeners with the same priority will be called in the order they have been subscribed. 
You can entirely omit priority parameter as it defaults to **0**.

### addListener

First, by using `addListener` method on `Koriit\EventDispatcher\EventDispatcher` object.
```php
interface EventDispatcherInterface
{
  // ..

  /**
   * Subscribes a listener to given event with specific priority.
   *
   * Listener must be invokable by PHP-DI.
   *
   * The higher the priority value the later the listener will be called.
   * Listeners with the same priority will be called in the order they have been subscribed.
   *
   * @param mixed  $eventName
   * @param mixed  $listener
   * @param number $priority
   */
  public function addListener($eventName, $listener, $priority = 0);

  // ...
}
```

### addListeners

Second, by using `addListeners` method on `Koriit\EventDispatcher\EventDispatcher` object.
```php
interface EventDispatcherInterface
{
  // ..

  /**
   * Subscribes listeners en masse.
   *
   * Listeners array is simple structure of 3 levels.
   * At first level it is associative array where keys are names of registered events.
   * At second level it is indexed array where keys are priority values.
   * At third level it is simple list containing listeners subscribed to given event with given priority.
   *
   * @param array $listeners
   */
  public function addListeners($listeners);

  // ...
}
```
Listeners array is simple structure of 3 levels. At first level it is associative array where keys are 
names of registered events. At second level it is indexed array where keys are priority values. 
At third level it is simple list containing listeners subscribed to given event with given priority.

Example:
```php
// listners.php
// namespace and imports...

return [
    CLIApplicationLifecycle::INITIALIZING => [
        0 => [
            [ConfigServiceInterface::class, 'init'],
        ],
        1 => [
            [CommandsServiceInterface::class, 'load'],
        ],
    ],

    CLIApplicationLifecycle::FINALIZING => [
        100 => [
            function (LoggerInterface $logger, InputInterface $input, $exitCode) {
                $logger->info('Returning from command `'.$input.'` with exit code '.$exitCode);
            },
        ],
    ],
];
```
```php
// ...

$dispatcher->addListeners(include 'listeners.php');

// ...
```

Dispatchment
------------
Dispatchment is a simple process of invoking all listeners subscribed to dispatched event.

```php
interface EventDispatcherInterface
{
    // ...

    /**
     * Dispatches an event with given name.
     *
     * @param mixed $eventName
     * @param array $parameters
     *
     * @return EventContextInterface
     */
    public function dispatch($eventName, $parameters = []);

    // ...
}
```
```php
// ..

$dispatcher->dispatch(ApplicationLifecycle::INITIALIZING);
```

### Stopping dispatchment
If any listener in the dispatchment chain returns a value that can be evaluated as *true*, 
the dispachment is stopped and all the remaining listeners are skipped. You can also achieve this by 
calling `stop` on event context.

While this design simplifies the process and __does not require wiring listeners with event dispatcher__, 
it makes it problematic to work with listeners that return values which cannot be interpreted as 
success or failure. This especially holds true for methods which allow for method chaining or implement 
fluent interface. To work around this problem you can use `stop` and `ignoreReturnValue` methods on
event context, though, that requires wiring your listener with event context. 
Everything is a trade-off, someone once said.  
### Context
Event context is simple data object holding information about the dispatchment process. 
See `Koriit\EventDispatcher\EventContextInterface` for more information.

### Parameters
You can pass additional parameters to be used by invoker while injecting listener arguments by name.
```php
// ..

$dispatcher->dispatch(ApplicationLifecycle::INITIALIZING, ["event" => new InitializationEvent()]);
```
```php
function listener($event) {
  // $event is InitializationEvent object
}
```

### eventName, eventContext, eventDispatcher
There are 3 parameters injected by event dispatcher itself:

1. eventName - name of the event dispatched
2. eventContext - reference to the context object of current dispatchment
3. eventDispatcher - reference to the event dispatcher itself; this allows for nested dispatchments

You cannot override them as using those keys in parameters array causes exception.
