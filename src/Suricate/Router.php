<?php
namespace Suricate;

/**
// TODO : handle closure
**/
class Router extends Service
{
    private $requestUri;
    private $routes;
    private $response;
    private $appMiddlewares = array(
        '\Suricate\Middleware\CheckMaintenance',
        );



    public function __construct()
    {
        $this->routes   = array();
        $this->response = Suricate::Response();
        $this->parseRequest();
    }

    public function configure($parameters = array())
    {
        foreach ($parameters as $routeData) {
            if (isset($routeData['target'])) {
                $handler = explode('::', $routeData['target']);
            } else {
                $handler = null;
            }

            $parameters = isset($routeData['parameters']) ? $routeData['parameters'] : array();
            

            if (isset($routeData['middleware'])) {
                $middleware = (array)$routeData['middleware'];
            } else {
                $middleware = array(); 
            }

            $this->addRoute(
                $routeData['path'],
                $handler,
                $parameters,
                array_merge($this->appMiddlewares, $middleware)
            );
        }
    }

    private function parseRequest()
    {
        $this->requestUri = Suricate::Request()->getRequestUri();
    }

    public function addRoute($routeName, $routeTarget, $parametersDefinitions, $middleware = null)
    {
        $this->routes[$routeName] = new Route($routeName, $this->requestUri, $routeTarget, $parametersDefinitions, $middleware);
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
                Suricate::Logger()->debug('Route "' . $route->getUrl() . '" matched, target: ' . json_encode($route->target));
                if (count($route->target) > 1) {
                    $callable = array(
                                    new $route->target[0]($this->response, $route),
                                    $route->target[1]
                                    );
                } else {
                    $callable = $route->target;
                }

                // We found a valid route
                if (is_callable($callable)) {
                    // We found a valid method for this controller
                    // Find parameters order
                    if (count($route->target) > 1) {
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
                } else {
                    Suricate::Logger()->debug('Route is not callable');
                }
            }
            if ($hasRoute) {
                // Middleware stack processing
                foreach ($route->middlewares as $middleware) {
                    if (is_object($middleware)) {
                        $middleware->handle($this->response);
                    } else {
                        with(new $middleware)->handle($this->response);
                    }
                }
                break;
            }
        }

        // No route matched
        if (!$hasRoute) {
            Suricate::Logger()->debug('No route found');
            $this->triggerError(404);
        }

        $this->response->write();
    }

    

    private function triggerError($errorCode)
    {
        $content = '';
        switch ($errorCode) {
            case 404:
                $message = 'HTTP/1.0 404 Not Found';
                $content = '<h1>404</h1>';
                break;
            case 401:
                $message = 'HTTP/1.0 401 Unauthorized';
                $content = '<h1>401</h1>';
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
