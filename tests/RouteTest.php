<?php
class RouteTest extends PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $name   = 'myRoute';
        $method = 'any';
        $path   = '/test-uri';
        $request = new Suricate\Request();
        $routeTarget = 'myController::myMethod';
        $parametersDefinitions = array();
        $middleware = null;

        $route = new Suricate\Route($name, $method, $path, $request, $routeTarget, $parametersDefinitions, $middleware);

        $this->assertEquals($path, $route->getPath());
    }
}