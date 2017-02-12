PHP-ED
======

Simple event dispatcher based on [PHP-DI](http://php-di.org).

This library **does not** aim to be general purpose library or cover all your possible needs. What this library **does** aim to be is perfect choice for those who use PHP-DI and perfer to use [PHP Definitions](http://php-di.org/doc/php-definitions.html).

Install
-------

PHP-ED is available via composer:

```sh
composer require koriit/php-ed
```

Tested with PHP-DI 5.4 and newer.

Usage
-----

Basic example:
```php
// configure and build your container

$dispatcher = new EventDispatcher($container);

$listener = function (LoggerInterface $logger, Request $request) {
    $logger->info($request->getMethod().' '.$request->getPathInfo());
};

$dispatcher->addListener(ApplicationLifecycle::INITIALIZING, $listener, 10);

$dispatcher->dispatch(ApplicationLifecycle::INITIALIZING);
```

Naturally since we are using PHP-DI then we would create a definition for `EventDispatcherInterface` and use:
```php
// ...

$dispatcher = $container->get(EventDispatcherInterface::class);

// ...
```

A listener can be anything that [can be invoked by PHP-DI](http://php-di.org/doc/container.html#call):
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
Naturally for above example to work you need to configure a definition for MyInterface.

Adding listeners
----------------
There are 2 ways to subscribe a listener. Both methods are declared in `EventDispatcherInterface`.

### addListener

First, by using `addListener` method on `EventDispatcher` object:
```php
// ...

$dispatcher->addListener($eventName, $listener, $priority);

// ...
```
You can omit priority parameter which defaults to **0**.

Second, by using `addListeners` method on `EventDispatcher` object:
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
