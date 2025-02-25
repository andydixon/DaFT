<?php

use PHPUnit\Framework\TestCase;
use Federaliser\ConfigParser;

class ConfigParserTest extends TestCase
{
    private $configParser;

    protected function setUp(): void
    {
        $this->configParser = new ConfigParser(__DIR__ .'/test-config.ini');
    }

    public function testParsesValidConfigFile()
    {
        $configFile = __DIR__ . '/mock-config.ini';
        file_put_contents($configFile, "[settings]\nkey=value");

        $cp = new ConfigParser($configFile);
        $result = $cp->getConfig();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('settings', $result);
        $this->assertEquals('value', $result['settings']['key']);

        unlink($configFile);
    }

    public function testThrowsExceptionOnInvalidFilePath()
    {
        $this->expectException(InvalidArgumentException::class);
        $cp = new ConfigParser('/invalid/path/to/config.ini');
        $result = $cp->getConfig();
    }

    public function testThrowsExceptionOnMalformedConfig()
    {
        $configFile = __DIR__ . '/malformed-config.ini';
        file_put_contents($configFile, "[settings\nkey=value"); // Missing closing bracket, nonsensical format

        $this->expectException(ParseError::class);
        
        $cp = new ConfigParser($configFile);
        $result = $cp->getConfig();

        unlink($configFile);
    }

    public function testHandlesEmptyConfigFile()
    {
        $configFile = __DIR__ . '/empty-config.ini';
        file_put_contents($configFile, "");

        $cp = new ConfigParser($configFile);
        $result = $cp->getConfig();

        $this->assertIsArray($result);
        $this->assertEmpty($result);

        unlink($configFile);
    }
}
