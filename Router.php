<?php
class Router
{
    private $routes = [];
    function setRoutes($routes)
    {
        $this->routes = $routes;
    }
    function getFileName($url)
    {
        foreach ($this->routes as $route => $value) {
            if (strpos($url, $route) !== false) {
                return $value;
            }
        }
    }
}