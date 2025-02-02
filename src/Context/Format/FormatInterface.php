<?php declare(strict_types=1);

namespace AP\Logger\Context\Format;

use Throwable;
use UnexpectedValueException;

interface FormatInterface
{
    /**
     * @param mixed $value
     * @return array|int|float|bool|string|null|NotFormatted
     */
    public function format(mixed $value): array|int|float|bool|string|null|NotFormatted;
}