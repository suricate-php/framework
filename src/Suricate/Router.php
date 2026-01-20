<?php

declare(strict_types=1);

namespace Suricate;

/**
// TODO : handle closure
 **/
class Router extends Service
{
    private $requestUri;
    private $baseUri;
    private $routes;
    private $response;
    private $appMiddlewares = ['\Suricate\Middleware\CheckMaintenance'];

    public function __construct()
    {
        parent::__construct();

        $this->routes = [];
        $this->response = Suricate::Response();
        $this->parseRequest();

        // Get app base URI, to transform real path before passing to route
        $this->baseUri = Suricate::App()->getParameter('base_uri');
    }

    public function configure($parameters = [])
    {
        foreach ($parameters as $routeName => $routeData) {
            if (isset($routeData['isRest']) && $routeData['isRest']) {
                $this->buildAndAddRestRoutes($routeName, $routeData);
            } else {
                $this->buildAndAddRoute($routeName, $routeData);
            }
        }
    }

    public function buildAndAddRoute(string $routeName, array $routeData)
    {
        $routeTarget = null;
        if (isset($routeData['target'])) {
            $routeTarget = explode('::', $routeData['target']);
        }

        $routeMethod = isset($routeData['method'])
            ? $routeData['method']
            : 'any';
        $parameters = isset($routeData['parameters'])
            ? $routeData['parameters']
            : [];

        $middleware = [];
        if (isset($routeData['middleware'])) {
            $middleware = (array) $routeData['middleware'];
        }

        $routePath = $routeData['path'];
        $routePrefix = $this->baseUri;
        if (isset($routeData['baseuri'])) {
            $routePrefix = $routeData['baseuri'];
            if (is_callable($routeData['baseuri'])) {
                $routePrefix = $routeData['baseuri']();
            }
        }

        // baseUri is / by default, if route in ini file are beginning with a '/',
        // strip the double slash here
        $computedRoutePath = str_replace('//', '/', $routePrefix . $routePath);

        $this->addRoute(
            $routeName,
            $routeMethod,
            $computedRoutePath,
            $routeTarget,
            $parameters,
            $middleware
        );
    }

    public function buildAndAddRestRoutes(string $routeBaseName, array $routeBaseData)
    {
        // If route has a parameters array defined, take the first defined
        // argument as ":id" parameter, and use key as parameter name
        // otherwise, default to id => [0-9]*
        if (
            isset($routeBaseData['parameters']) &&
            is_array($routeBaseData['parameters'])
        ) {
            reset($routeBaseData['parameters']);
            $primaryParameterName = key($routeBaseData['parameters']);

            $routeParameters = dataGet($routeBaseData, 'parameters', []);
        } else {
            $primaryParameterName = 'id';
            $primaryParameterPattern = '[0-9]*';

            $routeParameters = array_merge(
                [$primaryParameterName => $primaryParameterPattern],
                dataGet($routeBaseData, 'parameters', [])
            );
        }

        $resources = [
            'index' => ['method' => ['GET'], 'append' => ''],
            'create' => ['method' => ['GET'], 'append' => '/create'],
            'store' => ['method' => ['POST', 'OPTIONS'], 'append' => ''],
            'show' => [
                'method' => ['GET'],
                'append' => '/:' . $primaryParameterName
            ],
            'edit' => [
                'method' => ['GET'],
                'append' => '/:' . $primaryParameterName . '/edit'
            ],
            'update' => [
                'method' => ['PUT', 'OPTIONS'],
                'append' => '/:' . $primaryParameterName
            ],
            'patch' => [
                'method' => ['PATCH', 'OPTIONS'],
                'append' => '/:' . $primaryParameterName
            ],
            'destroy' => [
                'method' => ['DELETE', 'OPTIONS'],
                'append' => '/:' . $primaryParameterName
            ]
        ];

        foreach ($resources as $name => $definition) {
            $routeName = $routeBaseName . '.' . $name;
            $routeData = $routeBaseData;
            $routeData['method'] = $definition['method'];
            $routeData['path'] .= $definition['append'];
            $routeData['target'] .= '::' . $name;
            $routeData['parameters'] = $routeParameters;

            $this->buildAndAddRoute($routeName, $routeData);
        }
    }

    private function parseRequest()
    {
        $this->requestUri = Suricate::Request()->getRequestUri();
        $this->response->setRequestUri($this->requestUri);
    }

    public function addRoute(
        $routeName,
        $routeMethod,
        $routePath,
        $routeTarget,
        $parametersDefinitions,
        $middleware = null
    ) {
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

    public function addMiddleware(Middleware $middleware)
    {
        array_unshift($this->appMiddlewares, $middleware);

        return $this;
    }

    public function getMiddlewares()
    {
        return $this->appMiddlewares;
    }

    /**
     * Get router defined routes
     *
     * @return array
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    public function getResponse()
    {
        return $this->response;
    }
    /**
     * Loop through each defined routes, to find good one
     * @return void
     */
    public function doRouting()
    {
        $hasRoute = false;

        foreach ($this->routes as $route) {
            if ($route->isMatched) {
                $hasRoute = true;

                Suricate::Logger()->debug(
                    '[router] Route "' .
                        $route->getPath() .
                        '" matched, target: ' .
                        json_encode($route->target)
                );
                $result = $route->dispatch(
                    $this->response,
                    $this->appMiddlewares
                );
                if ($result === false) {
                    break;
                }
            }
        }

        // No route matched
        if (!$hasRoute) {
            Suricate::Logger()->debug('[router] No route found');
            app()->abort('404');
        }

        $this->response->write();
    }
}
