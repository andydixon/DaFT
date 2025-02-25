<?php

use Federaliser\Exporters\OpenmetricsExporter;
use PHPUnit\Framework\TestCase;
use Tests\Support\HeadersMock;

/**
 * Class OpenmetricsExporterTest
 *
 * Tests for OpenmetricsExporter.
 */
class OpenmetricsExporterTest extends TestCase
{
    protected function setUp(): void
    {
        HeadersMock::reset();
        OpenmetricsExporter::setHeaderHandler([HeadersMock::class, 'capture']);
    }

    public function testExportValidOpenMetrics()
    {
        $data = [
            ['label1' => 'value1', 'label2' => 'value2', 'metric' => 100],
            ['label1' => 'valueA', 'label2' => 'valueB', 'metric' => 200]
        ];

        $config = [
            'identifier' => 'example_metric'
        ];

        ob_start();
        OpenmetricsExporter::export($data, 200, $config);
        $output = ob_get_clean();

        $expectedOutput = "example_metric{label1=\"value1\",label2=\"value2\"} 100\n" .
                          "example_metric{label1=\"valueA\",label2=\"valueB\"} 200\n";

        $this->assertEquals($expectedOutput, $output);

        $headers = HeadersMock::getHeaders();
        $this->assertContains('Content-Type: text/plain', $headers);
    }

    public function testExportWithStatusCode()
    {
        $data = [
            ['label1' => 'value1', 'metric' => 100]
        ];

        $config = [
            'identifier' => 'example_metric'
        ];

        ob_start();
        OpenmetricsExporter::export($data, 201, $config);
        ob_get_clean();

        $headers = HeadersMock::getHeaders();
        $this->assertContains('Content-Type: text/plain', $headers);
    }
}
