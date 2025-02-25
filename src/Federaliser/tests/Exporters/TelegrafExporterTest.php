<?php

use Federaliser\Exporters\TelegrafExporter;
use PHPUnit\Framework\TestCase;

/**
 * Class TelegrafExporterTest
 *
 * Tests for TelegrafExporter JSON output and error handling.
 */
class TelegrafExporterTest extends TestCase
{
    public function testValidOutput()
    {
        $data = [
            ['metric' => 'cpu_usage', 'value' => 5],
            ['metric' => 'memory_usage', 'value' => 27]
        ];

        ob_start();
        TelegrafExporter::export($data);
        $output = ob_get_clean();

        $expected = json_encode([
            'cpu_usage' => 5,
            'memory_usage' => 27
        ]);

        $this->assertJsonStringEqualsJsonString($expected, $output);
    }

    public function testTooManyColumns()
    {
        $data = [
            ['metric' => 'cpu_usage', 'value' => 5, 'extra' => 99]
        ];

        ob_start();
        TelegrafExporter::export($data);
        $output = ob_get_clean();

        $this->assertStringContainsString('Too many columns', $output);
    }

    public function testNonNumericValue()
    {
        $data = [
            ['metric' => 'cpu_usage', 'value' => 'NaN']
        ];

        ob_start();
        TelegrafExporter::export($data);
        $output = ob_get_clean();

        $this->assertStringContainsString('non-numeric value', $output);
    }
}
