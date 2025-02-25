<?php

use Federaliser\Config\ConfigModel;
use PHPUnit\Framework\TestCase;

/**
 * Class ConfigModelTest
 *
 * Tests for ConfigModel CRUD operations and validation.
 */
class ConfigModelTest extends TestCase
{
    private string $testConfigFile;
    private ConfigModel $configModel;

    protected function setUp(): void
    {
        $this->testConfigFile = __DIR__ . '/test-config.ini';
        file_put_contents($this->testConfigFile, ""); // Create empty file
        $this->configModel = new ConfigModel($this->testConfigFile);
    }

    protected function tearDown(): void
    {
        if (file_exists($this->testConfigFile)) {
            unlink($this->testConfigFile);
        }
    }

    public function testCreateAndRetrieveConfig()
    {
        $data = [
            'identifier' => 'test_identifier',
            'type' => 'mysql',
            'source' => 'testsource.example.com'
        ];

        $this->configModel->create('test_section', $data);

        $config = $this->configModel->get('test_section');
        $this->assertEquals($data, $config);
    }

    public function testUpdateConfig()
    {
        $data = [
            'identifier' => 'test_identifier',
            'type' => 'mysql',
            'source' => 'testsource.example.com'
        ];

        $this->configModel->create('test_section', $data);

        $updatedData = [
            'identifier' => 'updated_identifier',
            'type' => 'mysql',
            'source' => 'updatedsource.example.com'
        ];

        $this->configModel->update('test_section', 'updated_section', $updatedData);

        $config = $this->configModel->get('updated_section');
        $this->assertEquals($updatedData, $config);
    }

    public function testDeleteConfig()
    {
        $data = [
            'identifier' => 'test_identifier',
            'type' => 'mysql',
            'source' => 'testsource.example.com'
        ];

        $this->configModel->create('test_section', $data);
        $this->configModel->delete('test_section');

        $config = $this->configModel->get('test_section');
        $this->assertNull($config);
    }

    public function testValidationException()
    {
        $this->expectException(RuntimeException::class);

        $data = [
            'identifier' => '', // Invalid identifier
            'type' => 'mysql',
            'source' => 'testsource.example.com'
        ];

        $this->configModel->create('invalid_section', $data);
    }
}
