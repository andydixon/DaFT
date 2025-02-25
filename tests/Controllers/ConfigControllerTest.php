<?php
/**
 * 
 * This is known to be broken, I'm learning how to do PHP Tests!!
 * 
 */
use Federaliser\Config\ConfigModel;
use Federaliser\Controller\ConfigController;
use PHPUnit\Framework\TestCase;

/**
 * Class ConfigControllerTest
 *
 * Tests for ConfigController CRUD operations.
 */
class ConfigControllerTest extends TestCase
{
    private $mockConfigModel;
    private ConfigController $configController;

    protected function setUp(): void
    {
        // Mock the ConfigModel
        $this->mockConfigModel = $this->createMock(ConfigModel::class);
        $this->configController = new ConfigController($this->mockConfigModel);
    }

    public function testIndex()
    {
        $expectedData = [
            'test_section' => [
                'identifier' => 'test_identifier',
                'type' => 'mysql',
                'source' => 'testsource.example.com'
            ]
        ];

        $this->mockConfigModel
            ->expects($this->once())
            ->method('getAll')
            ->willReturn($expectedData);

        $result = $this->configController->index();
        $this->assertEquals($expectedData, $result);
    }

    public function testShow()
    {
        $sectionName = 'test_section';
        $expectedData = [
            'identifier' => 'test_identifier',
            'type' => 'mysql',
            'source' => 'testsource.example.com'
        ];

        $this->mockConfigModel
            ->expects($this->once())
            ->method('get')
            ->with($sectionName)
            ->willReturn($expectedData);

        $result = $this->configController->show($sectionName);
        $this->assertEquals($expectedData, $result);
    }

    public function testCreate()
    {
        $sectionName = 'test_section';
        $data = [
            'identifier' => 'test_identifier',
            'type' => 'mysql',
            'source' => 'testsource.example.com'
        ];

        $this->mockConfigModel
            ->expects($this->once())
            ->method('create')
            ->with($sectionName, $data);

        $this->mockConfigModel
            ->expects($this->once())
            ->method('get')
            ->with($sectionName)
            ->willReturn($data);

        $result = $this->configController->create($sectionName, $data);
        $this->assertEquals($data, $result);
    }

    public function testUpdate()
    {
        $sectionName = 'test_section';
        $updatedSection = 'updated_section';
        $data = [
            'identifier' => 'updated_identifier',
            'type' => 'mysql',
            'source' => 'updatedsource.example.com'
        ];

        $this->mockConfigModel
            ->expects($this->once())
            ->method('get')
            ->with($sectionName)
            ->willReturn($data);

        $this->mockConfigModel
            ->expects($this->once())
            ->method('update')
            ->with($sectionName, $updatedSection, $data);

        $this->mockConfigModel
            ->expects($this->once())
            ->method('get')
            ->with($updatedSection)
            ->willReturn($data);

        $result = $this->configController->update($sectionName, $data);
        $this->assertEquals($data, $result);
    }

    public function testDelete()
    {
        $sectionName = 'test_section';
        $data = [
            'identifier' => 'test_identifier',
            'type' => 'mysql',
            'source' => 'testsource.example.com'
        ];

        $this->mockConfigModel
            ->expects($this->once())
            ->method('get')
            ->with($sectionName)
            ->willReturn($data);

        $this->mockConfigModel
            ->expects($this->once())
            ->method('delete')
            ->with($sectionName);

        $result = $this->configController->delete($sectionName);
        $this->assertTrue($result);
    }

    public function testDeleteNonExistentSection()
    {
        $sectionName = 'non_existent_section';

        $this->mockConfigModel
            ->expects($this->once())
            ->method('get')
            ->with($sectionName)
            ->willReturn(null);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Section not found: {$sectionName}");

        $this->configController->delete($sectionName);
    }
}
