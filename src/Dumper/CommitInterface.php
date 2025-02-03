<?php declare(strict_types=1);

namespace AP\Logger\Dumper;

/**
 * Defines a contract for committing log actions
 *
 * Implementing classes should ensure that all pending log entries
 * are processed and stored when this method is called
 */
interface CommitInterface
{
    /**
     * Commits any pending log entries
     */
    public function commit(): void;
}