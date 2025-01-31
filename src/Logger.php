<?php declare(strict_types=1);

namespace AP\Logger;

use AP\Logger\Dumper\AddInterface;
use AP\Logger\Dumper\CommitInterface;
use AP\Logger\Dumper\ErrorLog;

class Logger
{
    protected AddInterface $dumper;

    public function __construct(?AddInterface $dumper = null)
    {
        $this->dumper = is_null($dumper) ? new ErrorLog() : $dumper;
    }

    public function setDumper(AddInterface $dumper): static
    {
        $this->commit();
        $this->dumper = $dumper;
        return $this;
    }

    public function __destruct()
    {
        $this->commit();
    }

    public function add(Level $level, string $message, array $context = [], string $module = ""): static
    {
        $this->dumper->add(
            new Action(
                $level,
                $message,
                $context,
                $module,
            )
        );

        return $this;
    }

    public function commit(): static
    {
        if ($this->dumper instanceof CommitInterface) {
            $this->dumper->commit();
        }
        return $this;
    }
}