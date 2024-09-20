<?php
// making sure errors are displayed
ini_set('display_errors', 1);
error_reporting(E_ALL);
$url = $_SERVER["REQUEST_URI"];


function exception_handler($e) {
    $code = $e->getCode();
    $message = $e->getMessage();
    $response = [
        "status"=>$code,
        "message"=>$message
    ];
    http_response_code($e->getCode());
    echo json_encode($response);
}

set_exception_handler('exception_handler');
if (str_contains($url, "posts") == false && str_contains($url, "comments") == false) {
    throw new Exception("Invalid URL", 404);
}
require __DIR__.'/../core/bootstrap.php';
?>