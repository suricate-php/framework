<?php
namespace Fwk;

/**
// TODO : handle closure
**/
class Router extends Service
{
    private $requestUri;
    private $routes;
    private $response;



    public function __construct()
    {
        $this->routes   = array();
        $this->response = Fwk::Response();
        $this->parseRequest();
    }

    public function configure($parameters = array())
    {
        foreach ($parameters as $routeData) {
            if (isset($routeData['class']) && isset($routeData['method'])) {
                $handler = array($routeData['class'], $routeData['method']);
            } elseif (isset($routeData['function'])) {
                $handler = $routeData['function'];
            } else {
                throw new \Exception("Ni classe ni fonction, closure ? (" . json_encode($routeData) . ")");
            }

            if (!isset($routeData['parameters'])) {
                $parameters = array();
            } else {
                $parameters = $routeData['parameters'];
            }

            $this->addRoute(
                $routeData['path'],
                $handler,
                $parameters
            );
        }
    }

    private function parseRequest()
    {
        $this->requestUri = Fwk::Request()->getRequestUri();
    }

    public function addRoute($routeName, $routeTarget, $parametersDefinitions)
    {
        $this->routes[$routeName] = new Route($routeName, $this->requestUri, $routeTarget, $parametersDefinitions);
    }

    /**
     * Loop through each defined routes, to find good one
     * @return null
     */
    public function doRouting()
    {
        $hasRoute = false;
        foreach ($this->routes as $route) {
            if ($route->isMatched) {
                if (is_array($route->target)) {
                    $callable = array(
                                    new $route->target[0]($this->response),
                                    $route->target[1]
                                    );
                } else {
                    $callable = $route->target;
                }

                // We found a valid route
                if (is_callable($callable)) {
                    // We found a valid method for this controller
                    // Find parameters order
                    if (is_array($route->target)) {
                        $reflection = new \ReflectionMethod($route->target[0], $route->target[1]);
                    } else {
                        $reflection = new \ReflectionFunction($route->target);
                    }
                    $methodParameters = $reflection->getParameters();
                    $methodArguments = array();
                    
                    foreach ($methodParameters as $index => $parameter) {
                        if (isset($route->parametersValues[$parameter->name])) {
                            $methodArguments[$index] = urldecode($route->parametersValues[$parameter->name]);
                        } else {
                            // No value matching this parameter
                            $methodArguments[$index] = null;
                        }
                    }

                    // Calling $controller->method with arguments in right order
                    call_user_func_array($callable, $methodArguments);
                    $hasRoute = true;
                }
            }
            if ($hasRoute) {
                break;
            }
        }

        // No route matched
        if (!$hasRoute) {
            $this->triggerError(404);
        }

        $this->response->write();
    }

    

    private function triggerError($errorCode)
    {
        switch ($errorCode) {
            case 404:
                $message = 'HTTP/1.0 404 Not Found';
                $content = '<h1>404</h1>';
                break;
            default:
                $message = false;
                break;
        }

        if ($message !== false) {
            header($message);
            echo $content;
            die();
        }
    }
}
