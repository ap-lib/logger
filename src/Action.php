<?php declare(strict_types=1);

namespace AP\Logger;

readonly class Action
{
    public float $microtime;
    public array $backtrace;

    public function __construct(
        public Level  $level,
        public string $message,
        public array  $context = [],
        public string $module = "",
    )
    {
        $this->backtrace = array_slice(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS), 1);
        $this->microtime = microtime(true);
    }
}