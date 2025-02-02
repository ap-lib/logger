<?php declare(strict_types=1);

namespace AP\Logger;

class Log
{
    private const string DEFAULT_MODULE_NAME = 'app';

    protected static function defaultModule(): string
    {
        return self::DEFAULT_MODULE_NAME;
    }

    protected static ?Router $default = null;

    public static function router(): Router
    {
        if (is_null(self::$default)) {
            self::$default = new Router(self::DEFAULT_MODULE_NAME);
        }
        return self::$default;
    }

    final public static function add(Level $level, string $message, mixed $context, ?string $module): void
    {
        self::router()->add(
            $level,
            $message,
            $context,
            is_null($module) ? static::defaultModule() : $module,
        );
    }

    final public static function debug(string $message, mixed $context = [], ?string $module = null): void
    {
        self::add(Level::DEBUG, $message, $context, $module);
    }

    final public static function info(string $message, mixed $context = [], ?string $module = null): void
    {
        self::add(Level::INFO, $message, $context, $module);
    }

    final public static function warn(string $message, mixed $context = [], ?string $module = null): void
    {
        self::add(Level::WARNING, $message, $context, $module);
    }

    final public static function error(string $message, mixed $context = [], ?string $module = null): void
    {
        self::add(Level::ERROR, $message, $context, $module);
    }
}