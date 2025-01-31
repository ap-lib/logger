<?php

use AP\Logger\Action;
use AP\Logger\Dumper\AddInterface;
use AP\Logger\Dumper\CommitInterface;
use AP\Logger\Log;

require __DIR__ . "/../vendor/autoload.php";

class MyLogDumper implements AddInterface, CommitInterface
{
    private array $lines = [];

    public function __construct(
        readonly string $filename,
        readonly int    $batch_limit,
    )
    {
    }

    public function add(Action $action): void
    {
        print_r($this->lines);

        $this->lines[] = "$action->microtime [{$action->level->name}] $action->message";
        if (count($this->lines) == $this->batch_limit) {
            $this->commit();
        }
    }

    public function commit(): void
    {
        $content = "######################" . "\n";
        foreach ($this->lines as $line) {
            $content .= $line . "\n";
        }
        file_put_contents($this->filename, $content, FILE_APPEND);
        $this->lines = [];
    }
}


Log::setDefaultDumper(new MyLogDumper(
    filename: "logs.txt",
    batch_limit: 5
));

Log::info("hello 1");
Log::info("hello 2");
Log::info("hello 3");
Log::info("hello 4");
Log::info("hello 5");
Log::info("hello 6");
Log::info("hello 7");


echo file_get_contents("logs.txt");