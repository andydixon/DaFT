<?php

use PHPUnit\Framework\TestCase;
use Federaliser\ErrorHandler;
use Federaliser\Terminator;



class ErrorHandlerTest extends TestCase
{
    private $terminatorMock;
    private $errorHandler;

    protected function setUp(): void
    {
        $this->terminatorMock = $this->createMock(Terminator::class);
        $this->errorHandler = new ErrorHandler($this->terminatorMock);
    }

    public function testHandleErrorReturnsStructuredJsonResponse()
    {
        $errno = E_WARNING;
        $errstr = 'Test Warning';
        $errfile = 'testfile.php';
        $errline = 42;

        ob_start();
        $this->errorHandler->handleError($errno, $errstr, $errfile, $errline);
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertIsArray($response);
        $this->assertEquals('CORE_ERROR', $response['status']);
        $this->assertEquals('Test Warning', $response['message']);
        $this->assertEquals('testfile.php', $response['debug']['file']);
        $this->assertEquals(42, $response['debug']['line']);
    }

    public function testHandleErrorSetsHttpHeaders()
    {
        $errno = E_ERROR;
        $errstr = 'Test Error';
        $errfile = 'errorfile.php';
        $errline = 99;

        ob_start();
        $this->errorHandler->handleError($errno, $errstr, $errfile, $errline);
        ob_end_clean();

        $headers = headers_list();

        $this->assertContains('HTTP/1.1 500 Internal Server Error', $headers);
        $this->assertContains('Content-Type: application/json', $headers);
    }

    public function testHandleErrorDoesNotExitDuringTest()
    {
        $this->terminatorMock->expects($this->once())
                             ->method('terminate')
                             ->willReturn(null); // Mocking exit to do nothing

        $errno = E_WARNING;
        $errstr = 'Test Warning';
        $errfile = 'testfile.php';
        $errline = 42;

        ob_start();
        $this->errorHandler->handleError($errno, $errstr, $errfile, $errline);
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertEquals('CORE_ERROR', $response['status']);
        $this->assertEquals('Test Warning', $response['message']);
        $this->assertEquals('testfile.php', $response['debug']['file']);
        $this->assertEquals(42, $response['debug']['line']);
    }
}
