<?php declare(strict_types=1);

namespace AP\Logger\Normalizer;

use AP\Logger\Context\Format\NotFormatted;

interface Normalizer
{
    /**
     * @param mixed $value
     * @return array|int|float|bool|string|null|NotFormatted
     */
    public function normalize(mixed $value): array|int|float|bool|string|null|NotNormalized;
}