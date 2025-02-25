<?php

use Federaliser\Dataformats\FileXmlHandler;
use PHPUnit\Framework\TestCase;

/**
 * Class FileXmlHandlerTest
 *
 * Tests for FileXmlHandler.
 */
class FileXmlHandlerTest extends TestCase
{
    private string $testXmlFile;

    protected function setUp(): void
    {
        $this->testXmlFile = __DIR__ . '/test.xml';
        file_put_contents($this->testXmlFile, '<root><item>value1</item><item>value2</item></root>');
    }

    protected function tearDown(): void
    {
        if (file_exists($this->testXmlFile)) {
            unlink($this->testXmlFile);
        }
    }

    public function testHandleValidXmlFile()
    {
        $config = [
            'source' => $this->testXmlFile
        ];

        $handler = new FileXmlHandler($config);

        $expected = [
            'item' => ['value1', 'value2']
        ];

        $result = $handler->handle();

        $this->assertEquals($expected, $result);
    }

    public function testHandleInvalidXmlFile()
    {
        file_put_contents($this->testXmlFile, 'Invalid XML');

        $config = [
            'source' => $this->testXmlFile
        ];

        $handler = new FileXmlHandler($config);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Failed to parse XML");

        $handler->handle();
    }

    public function testHandleFileNotReadable()
    {
        $config = [
            'source' => '/path/to/nonexistent.xml'
        ];

        $handler = new FileXmlHandler($config);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Unable to read file");

        $handler->handle();
    }
}
