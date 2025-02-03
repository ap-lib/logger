<?php declare(strict_types=1);

namespace AP\Logger;

/**
 * Represents a logging action with metadata such as level, message, context, and backtrace
 */
readonly class Action
{
    public float $microtime;
    public array $context;
    public array $backtrace;

    /**
     * Creates a new Action instance
     *
     * @param Level $level Logging severity level
     * @param string $message Log message
     * @param array $context Additional contextual data
     * @param string $module Module name where the log event occurred
     */
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

    /**
     * Sanitizes the provided context by removing unsupported data types
     *
     * Only integers, booleans, floats, and strings are allowed.
     * Nested arrays are processed recursively.
     *
     * @param array $context Context data to be sanitized
     * @return array Sanitized context data
     */
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