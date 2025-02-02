<?php declare(strict_types=1);

namespace AP\Logger\Tests\Helpers;

use AP\Logger\Action;
use AP\Logger\Dumper\AddInterface;
use AP\Logger\Dumper\CommitInterface;

class ExampleDumper implements AddInterface, CommitInterface
{
    private array $lines = [];

    public function __construct(
        readonly string $filename,
        readonly int    $batch_limit,
        readonly string $separator
    )
    {
    }

    public function add(Action $action): void
    {
        $this->lines[] = "$action->microtime [$action->module:{$action->level->name}] $action->message";
        if (count($this->lines) == $this->batch_limit) {
            $this->commit();
        }
    }

    public function commit(): void
    {
        if (count($this->lines)) {
            $content = $this->separator . "\n";
            foreach ($this->lines as $line) {
                $content .= $line . "\n";
            }
            file_put_contents($this->filename, $content, FILE_APPEND);
            $this->lines = [];
        }
    }
}