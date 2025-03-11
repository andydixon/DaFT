<?php

use DaFT\Dataformats\WebCsvHandler;
use PHPUnit\Framework\TestCase;

/**
 * Class WebCsvHandlerTest
 *
 * Tests for WebCsvHandler.
 */
class WebCsvHandlerTest extends TestCase
{
    public function testHandleValidCsvFromUrl()
    {
        $url = 'https://people.sc.fsu.edu/~jburkardt/data/csv/airtravel.csv';

        $config = [
            'source' => $url
        ];

        $handler = new WebCsvHandler($config);

        $result = $handler->handle();

        $this->assertNotEmpty($result);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('JAN', $result[0]);
    }

    public function testHandleInvalidCsvFromUrl()
    {
        $url = 'https://jsonplaceholder.typicode.com/todos/1';

        $config = [
            'source' => $url
        ];

        $handler = new WebCsvHandler($config);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Unable to parse CSV");

        $handler->handle();
    }

    public function testHandleUrlNotAccessible()
    {
        $url = 'https://nonexistent-url.com/data.csv';

        $config = [
            'source' => $url
        ];

        $handler = new WebCsvHandler($config);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Unable to fetch URL");

        $handler->handle();
    }
}
