# AP\Logger

[![MIT License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

A performance-focused and flexible PHP library for logging, supporting errors, warnings, info, and debug messages.

## Installation

```bash
composer require ap-lib/logger
```

## Features

- Supports log separation by modules
- Allows different logging strategies for each module
- Enables easy creation of custom log strategies

## Requirements

- PHP 8.3 or higher

## Getting started

### Log level

```php
use AP\Logger\Log;

Log::error("message");
Log::warn("message");
Log::info("message");
Log::debug("message");

```

### Additonal info and modules

```php
use AP\Logger\Log;

// some error
Log::error("message");

// some error with additional information
Log::error("message", [
    "foo1" => "boo1"
    "foo2" => "boo2"
]);

// error for some module
Log::error("message", module: "module_name");


// some error with additional information for some module
Log::error(
    "message", 
    [
        "foo1" => "boo1"
        "foo2" => "boo2"
    ],
    "module_name"
);
```

### For large modules, having a dedicated logger can be helpful

```php
class MyLog extends Log
{
    protected static function defaultModule(): string
    {
        return "myModule";
    }
}

MyLog::info("hello"); // 2025-01-31 23:34:05.778400 myModule::[INFO] hello
Log::info("hello"); // 2025-01-31 23:34:05.778446 app::[INFO] hello
```

### Custom dumper

This example demonstrates how to batch log entries to a remote server. For simplicity, it writes to a file, but the
approach can be adapted for network transmission.

```php
use AP\Logger\Action;
use AP\Logger\Dumper\AddInterface;
use AP\Logger\Dumper\CommitInterface;

class MyLogDumper implements AddInterface, CommitInterface
{
    private array $lines = [];

    public function __construct(
        readonly string $filename,
        readonly int $batch_limit,
    )
    {
    }

    public function add(Action $action): void
    {
        print_r($this->lines);

        $this->lines[] = "$action->microtime [{$action->level->name}] $action->message";
        if (count($this->lines) == $this->batch_limit) {
            $this->commit();
        }
    }

    public function commit(): void
    {
        $content = "######################" . "\n";
        foreach ($this->lines as $line) {
            $content .= $line . "\n";
        }
        file_put_contents($this->filename, $content, FILE_APPEND);
        $this->lines = [];
    }
}
```

```php
use AP\Logger\Log;

Log::router()->setDefaultDumper(new MyLogDumper(
    filename: "logs.txt",
    batch_limit: 5
));

Log::info("hello 1");
Log::info("hello 2");
Log::info("hello 3");
Log::info("hello 4");
Log::info("hello 5");
Log::info("hello 6");
Log::info("hello 7");

```

logs.txt file will include:

```
######################
1738367282.3243 [INFO] hello 1
1738367282.3243 [INFO] hello 2
1738367282.3243 [INFO] hello 3
1738367282.3243 [INFO] hello 4
1738367282.3243 [INFO] hello 5
######################
1738367282.3244 [INFO] hello 6
1738367282.3244 [INFO] hello 7
```

### Lazy load and use different dumpers for production and development environments

By default, error log output no included: debug info, trace and additional info
It is good practice to set up different log dumper for dev and production environment


Using Boot helpers is a good approach:
```php
use AP\Logger\Dumper\ErrorLog;
use AP\Logger\Level;
use AP\Logger\Log;
use AP\Logger\Router;

class Boot
{
    public static function isProduction(): bool
    {
        return false;
    }

    public static function initLog(Router $router)
    {
        if (self::isProduction()) {
            $router->setDefaultDumper(new MyLogDumper());
        } else {
            $router->setDefaultDumper(
                new ErrorLog(
                    log_level: Level::DEBUG,
                    print_context: true,
                    print_trace: true,
                    timezone: "pst"
                )
            );
        }
    }

    public static function initCore()
    {
        // Set up lazy initialization for the router, it'll initialize only if used
        Log::routerLazyInit([self::class, "initLog"]);
    }
}
```

Use it to pre-initialize the environment 
```php
Boot::initCore();

Log::info("hello world");
```

If you need to route logs to different dumpers based on log levels, you can implement a routing dumper.


### Exceptions normalizer included by default

```php
use AP\Logger\Log;

function test1()
{
    try {
        throw new RuntimeException("hello exception");
    } catch (Throwable $e) {
        Log::error("error", context: $e);
    }
}

function test2()
{
    try {
        throw new RuntimeException("hello exception");
    } catch (Throwable $e) {
        Log::error("error", context: [
            "place"     => "test2",
            "exception" => $e,
        ]);
    }
}

function main()
{
    test1();
    test2();
}


main();
```

result:
```bash
php /code/readme_example_exception.php

2025-02-03 22:32:31.657456 app::[ERROR] error
  data:
    [type] => RuntimeException
    [message] => hello exception
    [file] => /code/readme_example_exception.php
    [line] => 10
    [code] => 0
    
2025-02-03 22:32:31.657527 app::[ERROR] error
  data:
    [place] => test2
    [exception] => Array
        (
            [type] => RuntimeException
            [message] => hello exception
            [file] => /code/readme_example_exception.php
            [line] => 19
            [code] => 0
        )
```