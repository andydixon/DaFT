<?php

namespace Federaliser;

class ErrorHandler
{
    public static function handleError($errno, $errstr, $errfile, $errline): void
    {
        $response=["status"=>"CORE_ERROR","message"=>$errstr,"debug"=>["file"=>$errfile,"line"=>$errline]];
        header('HTTP/1.1 500 Internal Server Error');
        header('Content-Type: application/json');
        echo json_encode($response);
        die();
    }
}