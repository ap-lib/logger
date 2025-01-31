<?php declare(strict_types=1);

namespace AP\Logger;

use AP\Logger\Dumper\AddInterface;

class Log
{
    private static ?Logger $default = null;

    /**
     * @var array<string,Logger>
     */
    private static array $special = [];

    protected static function defaultModule(): string
    {
        return 'app';
    }

    final public static function setSpecialDumper(string $module, AddInterface $dumper): void
    {
        if (isset(self::$special[$module])) {
            self::$special[$module]->setDumper($dumper);
        } else {
            self::$special[$module] = new Logger($dumper);
        }
    }

    final public static function setDefaultDumper(AddInterface $dumper): void
    {
        if (self::$default instanceof Logger) {
            self::$default->setDumper($dumper);
        } else {
            self::$default = new Logger($dumper);
        }
    }

    private static function getLogger(string $module): Logger
    {
        if (isset(self::$special[$module])) {
            return self::$special[$module];
        }
        if (!(self::$default instanceof Logger)) {
            self::$default = new Logger();
        }
        return self::$default;
    }

    private static function add(Level $level, string $message, array $context, ?string $module): void
    {
        $module = is_null($module) ? static::defaultModule() : $module;

        self::getLogger($module)->add(
            $level,
            $message,
            $context,
            $module,
        );
    }

    final public static function debug(string $message, array $context = [], ?string $module = null): void
    {
        self::add(Level::DEBUG, $message, $context, $module);
    }

    final public static function info(string $message, array $context = [], ?string $module = null): void
    {
        self::add(Level::INFO, $message, $context, $module);
    }

    final public static function warn(string $message, array $context = [], ?string $module = null): void
    {
        self::add(Level::WARNING, $message, $context, $module);
    }

    final public static function error(string $message, array $context = [], ?string $module = null): void
    {
        self::add(Level::ERROR, $message, $context, $module);
    }
}