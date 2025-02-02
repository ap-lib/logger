<?php declare(strict_types=1);

namespace AP\Logger;

readonly class Action
{
    public float $microtime;
    public array $context;
    public array $backtrace;

    public function __construct(
        public Level  $level,
        public string $message,
        array         $context = [],
        public string $module = "",
    )
    {
        $this->context   = self::sanitizeContext($context);
        $this->backtrace = array_slice(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS), 1);
        $this->microtime = microtime(true);
    }

    private static function sanitizeContext(array $context): array
    {
        foreach ($context as $key => $value) {
            if (is_array($value)) {
                $context[$key] = self::sanitizeContext($value);
            } elseif (!is_int($value) && !is_bool($value) && !is_float($value) && !is_string($value)) {
                unset($context[$key]);
            }
        }
        return $context;
    }
}