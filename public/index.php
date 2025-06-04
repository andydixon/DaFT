<?php

require __DIR__ . '/../vendor/autoload.php';

use DaFT\Application;
use DaFT\ErrorHandler;

// Set up the error handler
if (isset($_GET['errordebug'])) {
    // If errordebug is set, catch everything (so that notices & warnings go to your handler)
    error_reporting(E_ALL);
} else {
    // If no errordebug, do NOT invoke handler for notices or warnings
    //   (so PHP will handle E_NOTICE/E_WARNING normally, or suppress if display_errors=Off)
    error_reporting(E_ALL & ~(E_WARNING | E_NOTICE));
}
set_error_handler([ErrorHandler::class, 'handleError']);

// Instantiate and run the application
$app = new Application(__DIR__ . '/../config.ini');
$app->run();
