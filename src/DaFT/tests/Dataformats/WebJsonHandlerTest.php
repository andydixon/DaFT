<?php

use DaFT\Dataformats\WebJsonHandler;
use PHPUnit\Framework\TestCase;

/**
 * Class WebJsonHandlerTest
 *
 * Tests for WebJsonHandler.
 */
class WebJsonHandlerTest extends TestCase
{
    public function testHandleValidJsonFromUrl()
    {
        $url = 'https://jsonplaceholder.typicode.com/todos/1';

        $config = [
            'source' => $url
        ];

        $handler = new WebJsonHandler($config);

        $result = $handler->handle();

        $this->assertArrayHasKey('userId', $result);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('title', $result);
        $this->assertArrayHasKey('completed', $result);
    }

    public function testHandleInvalidJsonFromUrl()
    {
        $url = 'https://jsonplaceholder.typicode.com/invalid-endpoint';

        $config = [
            'source' => $url
        ];

        $handler = new WebJsonHandler($config);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("JSON decode error");

        $handler->handle();
    }

    public function testHandleUrlNotAccessible()
    {
        $url = 'https://nonexistent-url.com/data.json';

        $config = [
            'source' => $url
        ];

        $handler = new WebJsonHandler($config);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Unable to fetch URL");

        $handler->handle();
    }
}
