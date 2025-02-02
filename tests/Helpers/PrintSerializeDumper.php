<?php declare(strict_types=1);

namespace AP\Logger\Tests\Helpers;

use AP\Logger\Action;
use AP\Logger\Dumper\AddInterface;
use AP\Logger\Dumper\CommitInterface;
use Closure;

class PrintSerializeDumper implements AddInterface, CommitInterface
{
    private array            $lines = [];
    readonly public ?Closure $actionRender;

    public function __construct(
        ?Closure                $actionRender = null,
        readonly private string $elementsSeparator = "\n",
        readonly private string $batchSeparator = "\n",
        readonly private int    $batchLimit = 1,
    )
    {
        $this->actionRender = is_null($actionRender) ?
            ActionSerializer::closureFromAction() : $actionRender;
    }

    public function add(Action $action): void
    {
        $this->lines[] = ($this->actionRender)($action);
        if (count($this->lines) == $this->batchLimit) {
            $this->commit();
        }
    }

    public function commit(): void
    {
        if (count($this->lines)) {
            echo implode($this->elementsSeparator, $this->lines) . $this->batchSeparator;
        }
        $this->lines = [];
    }
}