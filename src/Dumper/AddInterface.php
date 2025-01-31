<?php declare(strict_types=1);

namespace AP\Logger\Dumper;

use AP\Logger\Action;

interface AddInterface
{
    public function add(Action $action): void;
}