<?php

use DaFT\Dataformats\AppJsonHandler;
use PHPUnit\Framework\TestCase;

/**
 * Class AppJsonHandlerTest
 *
 * Tests for AppJsonHandler.
 */
class AppJsonHandlerTest extends TestCase
{
    public function testHandleValidJsonOutput()
    {
        $config = [
            'source' => 'echo \'{"key1": "value1", "key2": "value2"}\''
        ];

        $handler = new AppJsonHandler($config);

        $expected = [
            'key1' => 'value1',
            'key2' => 'value2'
        ];

        $result = $handler->handle();

        $this->assertEquals($expected, $result);
    }

    public function testHandleInvalidJsonOutput()
    {
        $config = [
            'source' => 'echo "This is not JSON"'
        ];

        $handler = new AppJsonHandler($config);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("JSON decode error");

        $handler->handle();
    }

    public function testHandleCommandExecutionFailure()
    {
        $config = [
            'source' => 'invalid-command'
        ];

        $handler = new AppJsonHandler($config);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Command execution failed");

        $handler->handle();
    }
}
