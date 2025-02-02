<?php declare(strict_types=1);

namespace AP\Logger\Dumper;

use AP\Logger\Action;
use AP\Logger\Level;
use DateTime;
use DateTimeZone;
use Throwable;

readonly class ErrorLog implements AddInterface
{
    public function __construct(
        public Level   $log_level = Level::INFO,
        public bool    $print_context = true,
        public bool    $print_trace = false,
        public ?string $timezone = null,
        public string  $date_format = "Y-m-d H:i:s.u",
    )
    {
    }

    private function formatTime(float $microtime): string
    {
        $dt = DateTime::createFromFormat(
            'U.u',
            number_format(
                $microtime,
                6,
                '.',
                ''
            )
        );

        if (is_string($this->timezone)) {
            try {
                $dt->setTimeZone(new DateTimeZone($this->timezone));
            } catch (Throwable) {
            }
        }

        return $dt->format($this->date_format);
    }

    public function add(Action $action): void
    {
        $time    = $this->formatTime($action->microtime);
        $level   = $action->level->name;
        $message = ["$time $action->module::[$level] $action->message"];

        if ($this->print_context && count($action->context)) {
            $message[] = "  data:";
            $message[] = substr(print_r($action->context, true), 8, -3);
        }

        if ($this->print_trace) {
            $indent    = str_repeat(" ", 4) . "- ";
            $message[] = "  trace:";
            $message[] =
                $indent . implode("\n$indent",
                    array_map(
                        function ($el) {
                            return ($el['file'] ?? "") . ":" . ($el['line'] ?? "0");
                        },
                        $action->backtrace
                    ),
                ) . "\n";
        }

        if ($action->level->value >= $this->log_level->value) {
            error_log(implode("\n", $message));
        }
    }
}