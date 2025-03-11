<?php

require __DIR__ . '/../vendor/autoload.php';

use DaFT\Application;
use DaFT\ErrorHandler;

// Set up the error handler
set_error_handler([ErrorHandler::class, 'handleError']);

// Instantiate and run the application
$app = new Application(__DIR__ . '/../config.ini');
$app->run();
