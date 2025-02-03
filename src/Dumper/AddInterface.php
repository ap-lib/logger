<?php declare(strict_types=1);

namespace AP\Logger\Dumper;

use AP\Logger\Action;

/**
 * Defines a contract for adding log actions
 */
interface AddInterface
{
    /**
     * Adds a log action to the implementing logging system
     *
     * @param Action $action The log action to be recorded
     */
    public function add(Action $action): void;
}