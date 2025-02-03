<?php declare(strict_types=1);

namespace AP\Logger\Tests;

use AP\Logger\Dumper\ErrorLog;
use AP\Logger\Level;
use AP\Logger\Log;
use AP\Logger\Router;
use PHPUnit\Framework\TestCase;

/**
 * @runTestsInSeparateProcesses
 */
final class LazyLoadArrayTest extends TestCase
{

    public static function lazyLoad(Router $router): void
    {
        $router->setDefaultDumper(new ErrorLog(
            Level::INFO,
        ));
        echo "init router";
    }

    public function testLazyString(): void
    {
        ob_start();
        Log::routerLazyInit([self::class, "lazyLoad"]);
        $this->assertEquals("", ob_get_clean());

        ob_start();
        Log::debug("some skipped debug message");
        $this->assertEquals("init router", ob_get_clean());
    }

}
