<?php

use Federaliser\Dataformats\AppXmlHandler;
use PHPUnit\Framework\TestCase;

/**
 * Class AppXmlHandlerTest
 *
 * Tests for AppXmlHandler.
 */
class AppXmlHandlerTest extends TestCase
{
    public function testHandleValidXmlOutput()
    {
        $config = [
            'source' => 'echo "<root><item>value1</item><item>value2</item></root>"'
        ];

        $handler = new AppXmlHandler($config);

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

        $handler = new AppXmlHandler($config);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Failed to parse XML output");

        $handler->handle();
    }

    public function testHandleCommandExecutionFailure()
    {
        $config = [
            'source' => 'invalid-command'
        ];

        $handler = new AppXmlHandler($config);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Command execution failed");

        $handler->handle();
    }
}
