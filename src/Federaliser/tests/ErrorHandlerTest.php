<?php

use PHPUnit\Framework\TestCase;
use Federaliser\ErrorHandler;
use Psr\Log\LoggerInterface;

class ErrorHandlerTest extends TestCase
{
    private $loggerMock;
    private $errorHandler;

    protected function setUp(): void
    {
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->errorHandler = new ErrorHandler($this->loggerMock);
    }

    public function testHandlesExceptionAndReturnsJsonResponse()
    {
        $exception = new Exception('Test Exception', 500);

        $response = $this->errorHandler->handleException($exception);

        $this->assertIsArray($response);
        $this->assertEquals(500, $response['status']);
        $this->assertEquals('Test Exception', $response['message']);
    }

    public function testHandlesCustomExceptionWithSpecificStatusCode()
    {
        $exception = new InvalidArgumentException('Invalid Input', 400);

        $response = $this->errorHandler->handleException($exception);

        $this->assertEquals(400, $response['status']);
        $this->assertEquals('Invalid Input', $response['message']);
    }

    public function testLogsErrorWhenExceptionOccurs()
    {
        $exception = new Exception('Logging Test', 500);

        $this->loggerMock->expects($this->once())
                         ->method('error')
                         ->with('Logging Test', ['exception' => $exception]);

        $this->errorHandler->handleException($exception);
    }

    public function testHandlesUnhandledExceptionWithGenericError()
    {
        $exception = new RuntimeException('Unhandled Exception');

        $response = $this->errorHandler->handleException($exception);

        $this->assertEquals(500, $response['status']);
        $this->assertEquals('An unexpected error occurred.', $response['message']);
    }
}
