<?php declare(strict_types=1);

namespace AP\Logger\Tests\Helpers;

use AP\Logger\Log;

class MyLog extends Log
{
    public const string MY_MODULE_NAME = 'my';

    protected static function defaultModule(): string
    {
        return "my";
    }
}
