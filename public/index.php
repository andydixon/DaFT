<?php

require __DIR__ . '/../vendor/autoload.php';

use Federaliser\Application;
use Federaliser\ErrorHandler;

// Set up the error handler
set_error_handler([ErrorHandler::class, 'handleError']);

// Instantiate and run the application
$app = new Application(__DIR__ . '/../config.ini');
$app->run();
