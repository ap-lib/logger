<?php declare(strict_types=1);

namespace AP\Logger\Normalizer;

class Simple implements Normalizer
{
    public function normalize(mixed $value): array|string|int|float|bool|null|NotNormalized
    {
        if (is_string($value) || is_int($value) || is_float($value) || is_bool($value) || is_null($value)) {
            return $value;
        } elseif (is_array($value)) {
            foreach ($value as $k => $v) {
                $v = Simple::normalize($v);
                if ($v instanceof NotNormalized) {
                    unset($value[$k]);
                } else {
                    $value[$k] = $v;
                }
            }
            return $value;
        }
        return new NotNormalized();
    }
}