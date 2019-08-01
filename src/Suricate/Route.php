<?php

declare(strict_types=1);

namespace Suricate;

class Route
{
    private $name;
    private $method = [];
    private $path;
    private $computedPath;

    private $request;

    private $parametersDefinitions;
    public $parametersValues;

    public $isMatched;
    public $target;
    public $middlewares = [];

    public function __construct(
        $name,
        $method,
        $path,
        Request $request,
        $routeTarget,
        $parametersDefinitions = [],
        $middleware = null
    ) {
        $this->isMatched = false;
        $this->name = $name;
        $this->method = array_map('strtolower', (array) $method);
        $this->path = $path;
        $this->request = $request;
        $this->target = $routeTarget;
        $this->parametersDefinitions = $parametersDefinitions;
        $this->parametersValues = [];
        $this->middlewares = (array) $middleware;

        $this->setParameters();
        $this->computePath();
        $this->match();
    }

    /**
     * Get route path
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    private function match()
    {
        $requestUri = $this->request->getRequestUri();
        $pos = strpos($requestUri, '?');
        if ($pos !== false) {
            $requestUri = substr($requestUri, 0, $pos);
        }

        if (
            $this->method === ['any'] ||
            in_array(strtolower($this->request->getMethod()), $this->method)
        ) {
            // requestUri is matching pattern, set as matched route
            if (
                preg_match(
                    '#^' . $this->computedPath . '$#',
                    $requestUri,
                    $matching
                )
            ) {
                foreach (
                    array_keys($this->parametersDefinitions)
                    as $currentParameter
                ) {
                    $this->parametersValues[$currentParameter] = isset(
                        $matching[$currentParameter]
                    )
                        ? $matching[$currentParameter]
                        : null;
                }

                $this->isMatched = true;
            }
        }
    }

    public function dispatch($response, $middlewares = [])
    {
        $result = false;
        $callable = $this->getCallable($response);
        if (is_callable($callable)) {
            $this->middlewares = array_merge($middlewares, $this->middlewares);

            // We found a valid method for this controller
            // Find parameters order
            $methodArguments = $this->getCallableArguments();

            // Calling $controller->method with arguments in right order

            // Middleware stack processing
            foreach ($this->middlewares as $middleware) {
                if (
                    is_object($middleware) &&
                    $middleware instanceof Middleware
                ) {
                    $middleware->call($this->request, $response);
                } else {
                    with(new $middleware())->call($this->request, $response);
                }
            }

            $result = call_user_func_array($callable, $methodArguments);
        }

        return $result;
    }

    private function getCallable($response)
    {
        if (count($this->target) > 1) {
            $callable = [
                new $this->target[0]($response, $this),
                $this->target[1]
            ];
        } else {
            $callable = $this->target;
        }

        return $callable;
    }

    private function getCallableArguments()
    {
        if (count($this->target) > 1) {
            $reflection = new \ReflectionMethod(
                $this->target[0],
                $this->target[1]
            );
        } else {
            $reflection = new \ReflectionFunction($this->target);
        }

        $methodParameters = $reflection->getParameters();
        $methodArguments = [];

        foreach ($methodParameters as $index => $parameter) {
            if (isset($this->parametersValues[$parameter->name])) {
                $methodArguments[$index] = urldecode(
                    $this->parametersValues[$parameter->name]
                );
            } else {
                // No value matching this parameter
                $methodArguments[$index] = null;
            }
        }

        return $methodArguments;
    }

    protected function setParameters()
    {
        // Get all route parameters
        preg_match_all('|:([\w]+)|', $this->path, $routeParameters);
        $routeParametersNames = $routeParameters[1];

        foreach ($routeParametersNames as $parameter) {
            // Patterns parameters are not set, considering implicit declaration
            if (!isset($this->parametersDefinitions[$parameter])) {
                $this->parametersDefinitions[$parameter] = '.*';
            }
        }
    }

    /**
     * Build PCRE pattern path, according to route parameters
     * @return null
     */
    protected function computePath()
    {
        $this->computedPath = $this->path;

        // Assigning parameters
        foreach (
            $this->parametersDefinitions
            as $parameterName => $parameterDefinition
        ) {
            $this->computedPath = str_replace(
                ':' . $parameterName,
                '(?<' . $parameterName . '>' . $parameterDefinition . ')',
                $this->computedPath
            );
        }
    }
}
