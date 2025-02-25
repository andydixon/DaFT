<?php

use Federaliser\Dataformats\StdoutHandler;
use PHPUnit\Framework\TestCase;

/**
 * Class StdoutHandlerTest
 *
 * Tests for StdoutHandler.
 */
class StdoutHandlerTest extends TestCase
{
    public function testHandleValidOutput()
    {
        $config = [
            'source' => 'echo "output line 1\noutput line 2"'
        ];

        $handler = new StdoutHandler($config);

        $result = $handler->handle();

        $this->assertCount(2, $result);
        $this->assertEquals('output line 1', $result[0]);
        $this->assertEquals('output line 2', $result[1]);
    }

    public function testHandleWithRegex()
    {
        $config = [
            'source' => 'echo "ID: 12345 Name: John Doe"',
            'query' => '/ID: (?<id>\d+) Name: (?<name>[A-Za-z\s]+)/'
        ];

        $handler = new StdoutHandler($config);

        $expected = [
            ['id' => '12345', 'name' => 'John Doe']
        ];

        $result = $handler->handle();

        $this->assertEquals($expected, $result);
    }

    public function testHandleCommandExecutionFailure()
    {
        $config = [
            'source' => 'invalid-command'
        ];

        $handler = new StdoutHandler($config);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Command execution failed");

        $handler->handle();
    }
}
