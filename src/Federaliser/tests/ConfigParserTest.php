<?php

use PHPUnit\Framework\TestCase;
use Federaliser\ConfigParser;

class ConfigParserTest extends TestCase
{
    private $configParser;

    protected function setUp(): void
    {
        $this->configParser = new ConfigParser('/path/to/config.ini');
    }

    public function testParsesValidConfigFile()
    {
        $configFile = __DIR__ . '/mock-config.ini';
        file_put_contents($configFile, "[settings]\nkey=value");

        $result = $this->configParser->parse($configFile);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('settings', $result);
        $this->assertEquals('value', $result['settings']['key']);

        unlink($configFile);
    }

    public function testThrowsExceptionOnInvalidFilePath()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->configParser->parse('/invalid/path/to/config.ini');
    }

    public function testThrowsExceptionOnMalformedConfig()
    {
        $configFile = __DIR__ . '/malformed-config.ini';
        file_put_contents($configFile, "[settings\nkey=value"); // Missing closing bracket

        $this->expectException(ParseError::class);
        $this->configParser->parse($configFile);

        unlink($configFile);
    }

    public function testHandlesEmptyConfigFile()
    {
        $configFile = __DIR__ . '/empty-config.ini';
        file_put_contents($configFile, "");

        $result = $this->configParser->parse($configFile);

        $this->assertIsArray($result);
        $this->assertEmpty($result);

        unlink($configFile);
    }
}
