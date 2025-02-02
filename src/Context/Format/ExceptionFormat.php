<?php declare(strict_types=1);

namespace AP\Logger\Context\Format;

use Throwable;

class ExceptionFormat implements FormatInterface
{
    /**
     * @param mixed $value
     * @return array|int|float|bool|string|null|NotFormatted
     */
    public function format(mixed $value): array|int|float|bool|string|null|NotFormatted
    {
        return $value instanceof Throwable ? self::f($value) : new NotFormatted;
    }

    static public function f(Throwable $e): array
    {
        return [
            'message' => $e->getMessage(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
            'code'    => $e->getCode(),
            'trace'   => $e->getTrace(),
        ];
    }
}