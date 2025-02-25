<?php

use Federaliser\Dataformats\MysqlHandler;
use PHPUnit\Framework\TestCase;
use PDO;
use PDOStatement;
use PDOException;

/**
 * Class MysqlHandlerTest
 *
 * Tests for MysqlHandler.
 */
class MysqlHandlerTest extends TestCase
{
    private $pdoMock;
    private $stmtMock;

    protected function setUp(): void
    {
        $this->pdoMock = $this->createMock(PDO::class);
        $this->stmtMock = $this->createMock(PDOStatement::class);
    }

    public function testHandleSuccessfulQuery()
    {
        $config = [
            'source' => 'localhost',
            'port' => 3306,
            'default_db' => 'test_db',
            'username' => 'test_user',
            'password' => 'test_pass',
            'query' => 'SELECT * FROM test_table'
        ];

        $this->pdoMock->expects($this->once())
            ->method('query')
            ->with($config['query'])
            ->willReturn($this->stmtMock);

        $this->stmtMock->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn([['id' => 1, 'name' => 'test']]);

        $handler = new MysqlHandler($config);
        $result = $handler->handle();

        $this->assertEquals([['id' => 1, 'name' => 'test']], $result);
    }

    public function testHandleQueryFailure()
    {
        $config = [
            'source' => 'localhost',
            'port' => 3306,
            'default_db' => 'test_db',
            'username' => 'test_user',
            'password' => 'test_pass',
            'query' => 'SELECT * FROM invalid_table'
        ];

        $this->pdoMock->expects($this->once())
            ->method('query')
            ->will($this->throwException(new PDOException("Query Error")));

        $handler = new MysqlHandler($config);

        $result = $handler->handle();

        $this->assertArrayHasKey('error', $result);
        $this->assertStringContainsString('Query Error', $result['error']);
    }
}
