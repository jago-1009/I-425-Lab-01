<?php
// making sure errors are displayed
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$url = $_SERVER["REQUEST_URI"];


function exception_handler($e) {
    $debugBool = true;
    $code = $e->getCode();
    $message = $e->getMessage();
    $line = $e->getLine();
    $file = $e->getFile();

    if ($debugBool) {
        $response = [
            "status"=>$code,
            "message"=>$message,
            "line"=>$line,
            "file"=>$file
        ];

    }
    else {
        $response = [
            "status"=>$code,
            "message"=>$message,
        ];
    }

    http_response_code($e->getCode());
    echo json_encode($response);
}

function handle_statement($statement) {


}
set_exception_handler('exception_handler');

if (!str_contains($url, "posts") && !str_contains($url, "comments") || $url == "") {
    throw new Exception("Endpoint not found", 404);
}
require __DIR__.'/../core/bootstrap.php';
?>