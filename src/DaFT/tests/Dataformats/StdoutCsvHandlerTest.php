<?php

use DaFT\Dataformats\StdoutCsvHandler;
use PHPUnit\Framework\TestCase;

/**
 * Class StdoutCsvHandlerTest
 *
 * Tests for StdoutCsvHandler.
 */
class StdoutCsvHandlerTest extends TestCase
{
    public function testHandleValidCsvOutput()
    {
        $config = [
            'source' => 'echo "key,value\nkey1,value1\nkey2,value2"'
        ];

        $handler = new StdoutCsvHandler($config);

        $expected = [
            ['key' => 'key1', 'value' => 'value1'],
            ['key' => 'key2', 'value' => 'value2']
        ];

        $result = $handler->handle();

        $this->assertEquals($expected, $result);
    }

    public function testHandleInvalidCsvOutput()
    {
        $config = [
            'source' => 'echo "This is not CSV"'
        ];

        $handler = new StdoutCsvHandler($config);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Unable to parse CSV");

        $handler->handle();
    }

    public function testHandleCommandExecutionFailure()
    {
        $config = [
            'source' => 'invalid-command'
        ];

        $handler = new StdoutCsvHandler($config);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Command execution failed");

        $handler->handle();
    }
}
