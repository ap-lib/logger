<?php declare(strict_types=1);

namespace AP\Logger;

use AP\Logger\Dumper\AddInterface;
use AP\Logger\Dumper\CommitInterface;
use AP\Logger\Dumper\ErrorLog;

/**
 * Handles logging by delegating log entries to a dumper
 * Supports dynamic dumper assignment and ensures logs are committed on destruction
 */
class Logger
{
    protected AddInterface $dumper;

    /**
     * Initializes the logger with an optional dumper
     *
     * @param AddInterface|null $dumper Dumper instance for handling log entries
     *                                  Defaults to ErrorLog if not provided
     */
    public function __construct(?AddInterface $dumper = null)
    {
        $this->dumper = is_null($dumper) ? new ErrorLog() : $dumper;
    }

    /**
     * Sets a new dumper and commits any pending logs before switching
     *
     * @param AddInterface $dumper New dumper instance
     * @return static
     */
    public function setDumper(AddInterface $dumper): static
    {
        $this->commit();
        $this->dumper = $dumper;
        return $this;
    }

    /**
     * Commits any pending logs when the logger is destroyed
     */
    public function __destruct()
    {
        $this->commit();
    }

    /**
     * Adds a new log entry
     *
     * @param Level $level Logging severity level
     * @param string $message Log message
     * @param array $context Additional contextual data
     * @param string $module Module name
     * @return static
     */
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

    /**
     * Commits any pending log entries if the dumper supports committing
     *
     * @return static
     */
    public function commit(): static
    {
        if ($this->dumper instanceof CommitInterface) {
            $this->dumper->commit();
        }
        return $this;
    }
}