<?php

use DaFT\Dataformats\WebXmlHandler;
use PHPUnit\Framework\TestCase;

/**
 * Class WebXmlHandlerTest
 *
 * Tests for WebXmlHandler.
 */
class WebXmlHandlerTest extends TestCase
{
    public function testHandleValidXmlFromUrl()
    {
        $url = 'https://www.w3schools.com/xml/note.xml';

        $config = [
            'source' => $url
        ];

        $handler = new WebXmlHandler($config);

        $result = $handler->handle();

        $this->assertArrayHasKey('to', $result);
        $this->assertArrayHasKey('from', $result);
        $this->assertArrayHasKey('heading', $result);
        $this->assertArrayHasKey('body', $result);
    }

    public function testHandleInvalidXmlFromUrl()
    {
        $url = 'https://www.w3schools.com/xml/invalid.xml';

        $config = [
            'source' => $url
        ];

        $handler = new WebXmlHandler($config);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Failed to parse XML");

        $handler->handle();
    }

    public function testHandleUrlNotAccessible()
    {
        $url = 'https://nonexistent-url.com/data.xml';

        $config = [
            'source' => $url
        ];

        $handler = new WebXmlHandler($config);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Unable to fetch URL");

        $handler->handle();
    }
}
