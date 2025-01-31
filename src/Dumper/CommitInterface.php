<?php declare(strict_types=1);

namespace AP\Logger\Dumper;

interface CommitInterface
{
    public function commit(): void;
}