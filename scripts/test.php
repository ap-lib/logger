<?php

use AP\Logger\Action;
use AP\Logger\Dumper\AddInterface;
use AP\Logger\Dumper\CommitInterface;
use AP\Logger\Log;

require __DIR__ . "/../vendor/autoload.php";

class MyLogDumper implements AddInterface, CommitInterface
{

}

class MyLog extends Log
{
    protected static function defaultModule(): string
    {
        return "my";
    }
}

Log::router()->setDefaultDumper(new MyLogDumper(
    filename: "logs.txt",
    batch_limit: 5
));

Log::info("hello 1", module: "11");
Log::info("hello 2");
Log::info("hello 3");
MyLog::info("hello 4");
MyLog::info("hello 5");
MyLog::info("hello 6");
Log::info("hello 7");


echo file_get_contents("logs.txt");

//echo file_get_contents("logs.txt");

print_r(array_merge([1, 2], [3, 4]));