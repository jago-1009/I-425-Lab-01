<?php

require __DIR__ . '/DB.php'; //Require DB Class
require __DIR__ . '/Router.php'; //Require Router class
require __DIR__ . '/../routes.php'; //Require routes to indicate which file to server
require __DIR__ . '/../config.php'; //basic config for app

$router = new Router;
$router->setRoutes($routes);

$url = $_SERVER['REQUEST_URI'];
//require __DIR__ . '/../api/'.$router->getFileName($url);
//
require __DIR__.'/../api/posts.php';
require __DIR__.'/../api/comments.php';
//require __DIR__.'/../api/users.php';

?>

