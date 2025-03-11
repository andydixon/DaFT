<?php

use DaFT\Dataformats\StdoutXmlHandler;
use PHPUnit\Framework\TestCase;

/**
 * Class StdoutXmlHandlerTest
 *
 * Tests for StdoutXmlHandler.
 */
class StdoutXmlHandlerTest extends TestCase
{
    public function testHandleValidXmlOutput()
    {
        $config = [
            'source' => 'echo "<root><item>value1</item><item>value2</item></root>"'
        ];

        $handler = new StdoutXmlHandler($config);

        $expected = [
            'item' => ['value1', 'value2']
        ];

        $result = $handler->handle();

        $this->assertEquals($expected, $result);
    }

    public function testHandleInvalidXmlOutput()
    {
        $config = [
            'source' => 'echo "This is not XML"'
        ];

        $handler = new StdoutXmlHandler($config);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Failed to parse XML");

        $handler->handle();
    }

    public function testHandleCommandExecutionFailure()
    {
        $config = [
            'source' => 'invalid-command'
        ];

        $handler = new StdoutXmlHandler($config);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Command execution failed");

        $handler->handle();
    }
}
