<?php declare(strict_types=1);


namespace AP\Logger\Tests\Helpers;

use AP\Logger\Action;
use AP\Logger\Level;
use Closure;

class ActionSerializer
{
    public static function fromAction(Action $action): string
    {
        return self::fromParams(
            $action->message,
            $action->module,
            $action->level->value,
            $action->context,
        );
    }

    public static function fromParams(string $message, string $module = "app", int $level = Level::INFO->value, array $context = []): string
    {
        return json_encode([
            "module"  => $module,
            "level"   => $level,
            "message" => $message,
            "context" => $context,
        ]);
    }

    public static function closureFromAction(): Closure
    {
        return function (Action $action) {
            return ActionSerializer::fromAction($action);
        };
    }
}