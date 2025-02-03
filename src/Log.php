<?php declare(strict_types=1);

namespace AP\Logger;

use Closure;
use RuntimeException;

/**
 * Provides static methods for logging messages with different severity levels
 * Prioritizes performance by initializing the router lazily, only when it is used
 */
class Log
{
    private const string DEFAULT_MODULE_NAME = 'app';

    /**
     * Base default module name used when no module is specified
     * Can be overridden in classes that extend Log
     */
    protected static function defaultModule(): string
    {
        return self::DEFAULT_MODULE_NAME;
    }

    protected static ?Router                   $default         = null;
    protected static string|array|Closure|null $defaultLazyInit = null;

    /**
     * Returns the default router instance, creating it only when first accessed
     *
     * @return Router Router instance for handling log entries
     */
    public static function router(): Router
    {
        if (is_null(self::$default)) {
            self::$default = new Router(self::DEFAULT_MODULE_NAME);
            if (!is_null(self::$defaultLazyInit)) {
                (self::$defaultLazyInit)(self::$default);
            }
        }
        return self::$default;
    }

    /**
     * Sets a lazy initializer for the default router
     *
     * @param string|array|Closure $callable A valid callable that will be executed on router initialization
     *                                       The function must be in the format `fn(Router $router)`
     *                                       and should only configure the provided `$router` instance
     * @throws RuntimeException If the provided argument is not a valid callable
     */
    public static function routerLazyInit(string|array|Closure $callable): void
    {
        if (!is_callable($callable)) {
            throw new RuntimeException(var_export($callable, true) . " must be valid callable<string|array> or Closure");
        }
        self::$defaultLazyInit = $callable;
    }

    /**
     * Logs a message with the specified severity level
     *
     * @param Level $level Logging severity level
     * @param string $message Log message
     * @param mixed $context additional contextual data
     * @param string|null $module Module name, defaults to the default module if null
     */
    final public static function add(Level $level, string $message, mixed $context, ?string $module): void
    {
        self::router()->add(
            $level,
            $message,
            $context,
            is_null($module) ? static::defaultModule() : $module,
        );
    }

    /**
     * Logs a debug message: used for detailed debugging information
     *
     * @param string $message Log message
     * @param mixed $context Additional contextual data
     * @param string|null $module Module name
     */
    final public static function debug(string $message, mixed $context = [], ?string $module = null): void
    {
        self::add(Level::DEBUG, $message, $context, $module);
    }

    /**
     * Logs a debug message: used for general informational messages
     *
     * @param string $message Log message
     * @param mixed $context Additional contextual data
     * @param string|null $module Module name
     */
    final public static function info(string $message, mixed $context = [], ?string $module = null): void
    {
        self::add(Level::INFO, $message, $context, $module);
    }

    /**
     * Logs a debug message: used for potentially harmful situations
     *
     * @param string $message Log message
     * @param mixed $context Additional contextual data
     * @param string|null $module Module name
     */
    final public static function warn(string $message, mixed $context = [], ?string $module = null): void
    {
        self::add(Level::WARNING, $message, $context, $module);
    }

    /**
     * Logs a debug message: used for serious error events that require immediate attention
     *
     * @param string $message Log message
     * @param mixed $context Additional contextual data
     * @param string|null $module Module name
     */
    final public static function error(string $message, mixed $context = [], ?string $module = null): void
    {
        self::add(Level::ERROR, $message, $context, $module);
    }
}