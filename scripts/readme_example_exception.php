<?php

use AP\Logger\Log;

require __DIR__ . "/../vendor/autoload.php";

function test1()
{
    try {
        throw new RuntimeException("hello exception");
    } catch (Throwable $e) {
        Log::error("error", context: $e);
    }
}

function test2()
{
    try {
        throw new RuntimeException("hello exception");
    } catch (Throwable $e) {
        Log::error("error", context: [
            "place"     => "test2",
            "exception" => $e,
        ]);
    }
}

function main()
{
    test1();
    test2();
}

main();