<?php declare(strict_types=1);

namespace AP\Logger\Tests;

use AP\Logger\Action;
use AP\Logger\Context\Format\ExceptionFormat;
use AP\Logger\Level;
use AP\Logger\Log;
use AP\Logger\Tests\Helpers\ActionSerializer;
use AP\Logger\Tests\Helpers\ExampleDumper;
use AP\Logger\Tests\Helpers\MyLog;
use AP\Logger\Tests\Helpers\PrintSerializeDumper;
use Exception;
use PHPUnit\Framework\TestCase;

final class LogTest extends TestCase
{
    public function testLevels(): void
    {
        Log::router()->commitAll(); // clear
        Log::router()->setDefaultDumper(new PrintSerializeDumper());

        ob_start();
        Log::error("type error");
        Log::warn("type warn");
        Log::info("type info");
        Log::debug("type debug");
        Log::info("info by def");

        Log::router()->commitAll();
        $res = ob_get_clean();

        $this->assertEquals(
            implode("\n", [
                ActionSerializer::fromParams("type error", level: Level::ERROR->value),
                ActionSerializer::fromParams("type warn", level: Level::WARNING->value),
                ActionSerializer::fromParams("type info", level: Level::INFO->value),
                ActionSerializer::fromParams("type debug", level: Level::DEBUG->value),
                ActionSerializer::fromParams("info by def"),
                ""
            ]),
            $res
        );
    }

    public function testAdditionalAndModel(): void
    {
        Log::router()->commitAll(); // clear
        Log::router()->setDefaultDumper(new PrintSerializeDumper());

        ob_start();
        Log::info("message1");
        Log::info("message2", context: ["foo" => "boo", "foo1" => "boo1"]);
        Log::info("message3", module: "module_name");
        Log::info("message4", context: ["foo" => "boo", "foo2" => "boo2"], module: "module_name2");

        Log::router()->commitAll();
        $res = ob_get_clean();

        $this->assertEquals(
            implode("\n", [
                ActionSerializer::fromParams("message1"),
                ActionSerializer::fromParams("message2", context: ["foo" => "boo", "foo1" => "boo1"]),
                ActionSerializer::fromParams("message3", module: "module_name"),
                ActionSerializer::fromParams("message4", context: ["foo" => "boo", "foo2" => "boo2"], module: "module_name2"),
                ""
            ]),
            $res
        );
    }

    public function testDefaultModuleNameOnSeparateClass(): void
    {
        Log::router()->commitAll(); // clear
        Log::router()->setDefaultDumper(new PrintSerializeDumper());


        ob_start();

        Log::info("message1");
        Log::info("message2", module: "overrideDefault_1");
        MyLog::info("message3");
        MyLog::info("message4", module: "overrideDefault_2");

        Log::router()->commitAll();
        $res = ob_get_clean();

        $this->assertEquals(
            implode("\n", [
                ActionSerializer::fromParams("message1"),
                ActionSerializer::fromParams("message2", module: "overrideDefault_1"),
                ActionSerializer::fromParams("message3", module: MyLog::MY_MODULE_NAME),
                ActionSerializer::fromParams("message4", module: "overrideDefault_2"),
                ""
            ]),
            $res
        );
    }

    public function testBulk(): void
    {
        // clear log
        Log::router()->commitAll();

        Log::router()->setDefaultDumper(new PrintSerializeDumper(
            actionRender: ActionSerializer::closureFromAction(),
            elementsSeparator: "\n",
            batchSeparator: "\n\n\n",
            batchLimit: 5
        ));

        ob_start();

        Log::info("hello 1", module: "someModule");
        Log::info("hello 2");
        Log::info("hello 3");
        MyLog::info("hello 4");
        MyLog::info("hello 5", module: "overrideModule");

        MyLog::info("hello 6");
        Log::info("hello 7");

        Log::router()->commitAll();
        $res = ob_get_clean();

        $this->assertEquals(
            implode("\n", [
                ActionSerializer::fromParams("hello 1", module: "someModule"),
                ActionSerializer::fromParams("hello 2"),
                ActionSerializer::fromParams("hello 3"),
                ActionSerializer::fromParams("hello 4", module: MyLog::MY_MODULE_NAME),
                ActionSerializer::fromParams("hello 5", module: "overrideModule"),
                "",
                "",
                ActionSerializer::fromParams("hello 6", module: MyLog::MY_MODULE_NAME),
                ActionSerializer::fromParams("hello 7"),
                "",
                "",
                "",
            ]),
            $res
        );
    }

    public function testReadmeCustomDumper()
    {
        $separator = "######################";
        $filename  = "test-custom-dumper.log";
        file_put_contents($filename, "");

        Log::router()->setDefaultDumper(new ExampleDumper(
            filename: $filename,
            batch_limit: 5,
            separator: $separator,
        ));

        Log::info("hello1");
        Log::info("hello2");
        Log::info("hello3");
        Log::info("hello4");
        Log::info("hello5");
        Log::info("hello6");
        Log::info("hello7");

        Log::router()->commitAll();
        $log = file_get_contents($filename);
        unlink($filename);

        $log_lines    = explode("\n", $log);
        $linesInclude = [
            $separator,
            "hello1",
            "hello2",
            "hello3",
            "hello4",
            "hello5",
            $separator,
            "hello6",
            "hello7",
        ];


        $good    = true;
        $message = "";
        foreach ($linesInclude as $k => $mustInclude) {
            if (!isset($log_lines[$k])) {
                $message = "log no include line #$k";
                $good    = false;
                break;
            }

            if (strlen($mustInclude) && !str_contains($log_lines[$k], $mustInclude)) {
                $message = "log line #$k `$log_lines[$k]` must include string `$mustInclude`";
                $good    = false;
                break;
            }
        }

        $this->assertTrue($good, $message);
    }

    public function testSeparateDumpers()
    {
        // clear log
        Log::router()->commitAll();

        Log::router()->setDefaultDumper(new PrintSerializeDumper(
            actionRender: function (Action $action) {
                return "DEF: " . $action->message;
            },
            batchLimit: 3
        ));

        Log::router()->setSpecialDumper(
            "ok1",
            new PrintSerializeDumper(
                actionRender: function (Action $action) {
                    return "OK1: " . $action->message;
                },
                batchLimit: 2,
            ));

        Log::router()->setSpecialDumper(
            "ok2",
            new PrintSerializeDumper(
                actionRender: function (Action $action) {
                    return "OK2: " . $action->message;
                },
                batchLimit: 2,
            ));

        ob_start();

        Log::info("def 1 of 3");
        Log::info("def 2 of 3", module: "some");
        Log::info("ok1 1 of 2", module: "ok1");
        Log::info("ok1 2 of 2", module: "ok1");
        Log::info("ok2 1 of 2", module: "ok2");
        Log::info("ok1 1 of 2", module: "ok1");
        Log::info("ok2 2 of 2", module: "ok2");
        Log::info("def 3 of 3");

        Log::router()->commitAll();
        $res = ob_get_clean();

        $this->assertEquals(
            implode("\n", [
                "OK1: ok1 1 of 2",
                "OK1: ok1 2 of 2",

                "OK2: ok2 1 of 2",
                "OK2: ok2 2 of 2",

                "DEF: def 1 of 3",
                "DEF: def 2 of 3",
                "DEF: def 3 of 3",

                "OK1: ok1 1 of 2",

                "",
            ]),
            $res
        );
    }

    public function testExceptionFormatter()
    {
        // clear log
        Log::router()->commitAll();
        Log::router()->setDefaultDumper(new PrintSerializeDumper(
            actionRender: function (Action $action) {
                return json_encode($action->context);
            },
            batchSeparator: ""
        ));
        ob_start();

        try {
            throw new Exception("test exception");
        } catch (Exception $e) {
            $expected_output = json_encode(ExceptionFormat::f($e));
            Log::error("error", context: $e);
        }

        Log::router()->commitAll();
        $res = ob_get_clean();

        $this->assertEquals($expected_output, $res);
    }

    public function testNotFoundFormatter()
    {
        // clear log
        Log::router()->commitAll();
        Log::router()->setDefaultDumper(new PrintSerializeDumper(
            actionRender: function (Action $action) {
                return json_encode($action->context);
            },
            batchSeparator: ""
        ));
        ob_start();

        // any random object
        Log::error("error", context: new Action(
            level: Level::WARNING,
            message: "hello world",
        ));

        Log::router()->commitAll();
        $res = ob_get_clean();

        $this->assertEquals(json_encode([]), $res);
    }

    public function testNotFoundFormatterRecursive()
    {
        // clear log
        Log::router()->commitAll();
        Log::router()->setDefaultDumper(new PrintSerializeDumper(
            actionRender: function (Action $action) {
                return json_encode($action->context);
            },
            batchSeparator: ""
        ));
        ob_start();

        // any random object
        Log::error("error", context: [
            "foo"    => "boo",
            "object" => new Action(
                level: Level::WARNING,
                message: "hello world",
            )
        ]);

        Log::router()->commitAll();
        $res = ob_get_clean();

        $this->assertEquals(json_encode(["foo" => "boo"]), $res);
    }
}
