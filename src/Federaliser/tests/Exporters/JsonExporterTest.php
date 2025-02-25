<?php

use Federaliser\Exporters\JsonExporter;
use PHPUnit\Framework\TestCase;
use Tests\Support\HeadersMock;

/**
 * Class JsonExporterTest
 *
 * Tests for JsonExporter.
 */
class JsonExporterTest extends TestCase
{
    protected function setUp(): void
    {
        HeadersMock::reset();
        JsonExporter::setHeaderHandler([HeadersMock::class, 'capture']);
    }

    public function testExportValidJson()
    {
        $data = [
            'key1' => 'value1',
            'key2' => 2,
            'key3' => 3.14
        ];

        ob_start();
        JsonExporter::export($data);
        $output = ob_get_clean();

        $expectedJson = json_encode($data, JSON_NUMERIC_CHECK);

        $this->assertJsonStringEqualsJsonString($expectedJson, $output);

        $headers = HeadersMock::getHeaders();
        $this->assertContains('Content-Type: application/json; charset=utf-8', $headers);
    }

    public function testExportWithStatusCode()
    {
        $data = ['status' => 'success'];

        ob_start();
        JsonExporter::export($data, 201);
        ob_get_clean();

        $headers = HeadersMock::getHeaders();
        $this->assertContains('Content-Type: application/json; charset=utf-8', $headers);
    }
}
