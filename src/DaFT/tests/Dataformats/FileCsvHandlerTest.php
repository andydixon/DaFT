<?php

use DaFT\Dataformats\FileCsvHandler;
use PHPUnit\Framework\TestCase;

/**
 * Class FileCsvHandlerTest
 *
 * Tests for FileCsvHandler.
 */
class FileCsvHandlerTest extends TestCase
{
    private string $testCsvFile;

    protected function setUp(): void
    {
        $this->testCsvFile = __DIR__ . '/test.csv';
        file_put_contents($this->testCsvFile, "key,value\nkey1,value1\nkey2,value2");
    }

    protected function tearDown(): void
    {
        if (file_exists($this->testCsvFile)) {
            unlink($this->testCsvFile);
        }
    }

    public function testHandleValidCsvFile()
    {
        $config = [
            'source' => $this->testCsvFile
        ];

        $handler = new FileCsvHandler($config);

        $expected = [
            ['key' => 'key1', 'value' => 'value1'],
            ['key' => 'key2', 'value' => 'value2']
        ];

        $result = $handler->handle();

        $this->assertEquals($expected, $result);
    }

    public function testHandleFileNotReadable()
    {
        $config = [
            'source' => '/path/to/nonexistent.csv'
        ];

        $handler = new FileCsvHandler($config);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Unable to read file");

        $handler->handle();
    }
}
