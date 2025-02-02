<?php declare(strict_types=1);

namespace AP\Logger\Normalizer;

class Base implements Normalizer
{
    /**
     * @var array<Normalizer>
     */
    protected array $normalizers = [];

    public function __construct()
    {
    }

    public function normalize(mixed $value): array|string|int|float|bool|null|NotNormalized
    {
        if (is_string($value) || is_int($value) || is_float($value) || is_bool($value) || is_null($value)) {
            return $value;
        } elseif (is_array($value)) {
            foreach ($value as $k => $v) {
                $v = Base::normalize($v);
                if ($v instanceof NotNormalized) {
                    unset($value[$k]);
                } else {
                    $value[$k] = $v;
                }
            }
            return $value;
        }
        foreach ($this->normalizers as $normalizer) {
            $v = $normalizer->normalize($value);
            if (!($v instanceof NotNormalized)) {
                return $v;
            }
        }
        return new NotNormalized();
    }
}