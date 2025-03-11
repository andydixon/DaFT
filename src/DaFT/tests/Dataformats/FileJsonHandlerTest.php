<?php

use DaFT\Dataformats\FileJsonHandler;
use PHPUnit\Framework\TestCase;

/**
 * Class FileJsonHandlerTest
 *
 * Tests for FileJsonHandler.
 */
class FileJsonHandlerTest extends TestCase
{
    private string $testJsonFile;

    protected function setUp(): void
    {
        $this->testJsonFile = __DIR__ . '/test.json';
        file_put_contents($this->testJsonFile, '{"key1": "value1", "key2": "value2"}');
    }

    protected function tearDown(): void
    {
        if (file_exists($this->testJsonFile)) {
            unlink($this->testJsonFile);
        }
    }

    public function testHandleValidJsonFile()
    {
        $config = [
            'source' => $this->testJsonFile
        ];

        $handler = new FileJsonHandler($config);

        $expected = [
            'key1' => 'value1',
            'key2' => 'value2'
        ];

        $result = $handler->handle();

        $this->assertEquals($expected, $result);
    }

    public function testHandleInvalidJsonFile()
    {
        file_put_contents($this->testJsonFile, 'Invalid JSON');

        $config = [
            'source' => $this->testJsonFile
        ];

        $handler = new FileJsonHandler($config);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("JSON decode error");

        $handler->handle();
    }

    public function testHandleFileNotReadable()
    {
        $config = [
            'source' => '/path/to/nonexistent.json'
        ];

        $handler = new FileJsonHandler($config);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Unable to read file");

        $handler->handle();
    }
}
