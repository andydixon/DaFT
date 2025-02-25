<?php

require __DIR__ . '/../vendor/autoload.php';

use Federaliser\Application;
use Federaliser\ErrorHandler;
use Federaliser\Terminator;

// Set up the error handler with an over dramatic exit hack for tests
$terminator = new Terminator();
$errorHandler = new ErrorHandler($terminator);
set_error_handler([$errorHandler, 'handleError']);


// Instantiate and run the application
$app = new Application(__DIR__ . '/../config.ini');
$app->run();
