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
        foreach ($parameters as $routeName => $routeData) {
            if (isset($routeData['target'])) {
                $routeTarget = explode('::', $routeData['target']);
            } else {
                $routeTarget = null;
            }
            $routeMethod    = isset($routeData['method']) ? $routeData['method'] : 'any';
            $parameters     = isset($routeData['parameters']) ? $routeData['parameters'] : array();
            

            if (isset($routeData['middleware'])) {
                $middleware = (array)$routeData['middleware'];
            } else {
                $middleware = array(); 
            }

            $this->addRoute(
                $routeName,
                $routeMethod,
                $routeData['path'],
                $routeTarget,
                $parameters,
                array_merge($this->appMiddlewares, $middleware)
            );
        }
    }

    private function parseRequest()
    {
        $this->requestUri = Suricate::Request()->getRequestUri();
    }

    public function addRoute($routeName, $routeMethod, $routePath, $routeTarget, $parametersDefinitions, $middleware = null)
    {
        $this->routes[$routeName] = new Route(
            $routeName,
            $routeMethod,
            $routePath,
            Suricate::Request(),
            $routeTarget,
            $parametersDefinitions,
            $middleware
        );
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
                Suricate::Logger()->debug('Route "' . $route->getPath() . '" matched, target: ' . json_encode($route->target));

                $hasRoute = $route->dispatch($this->response);

                
                /*

                // We found a valid route
                if (is_callable($callable)) {
                    
                    // Calling $controller->method with arguments in right order
                    call_user_func_array($callable, $methodArguments);
                    $hasRoute = true;
                } else {
                    Suricate::Logger()->debug('Route is not callable');
                }*/
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
