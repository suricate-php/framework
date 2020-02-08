<?php
class RouteTest extends \PHPUnit\Framework\TestCase
{
    public function testConstruct()
    {
        $name = 'myRoute';
        $method = 'any';
        $path = '/test-uri';
        $request = new Suricate\Request();
        $routeTarget = 'myController::myMethod';
        $parametersDefinitions = [];
        $middleware = null;

        $route = new Suricate\Route(
            $name,
            $method,
            $path,
            $request,
            $routeTarget,
            $parametersDefinitions,
            $middleware
        );

        $this->assertEquals($path, $route->getPath());
        $this->assertEquals(['any'], $route->getMethod());
        $this->assertEquals($name, $route->getName());
    }
}
