<?php

declare(strict_types=1);

namespace Suricate\Console;

use Suricate\Console;
use Suricate\Suricate;

class Route
{
    protected $app;

    public function __construct(Suricate $app)
    {
        $this->app = $app;
    }

    /**
     * Execute command
     *
     * @return integer
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function execute(): int
    {
        $routes = $this->getRoutes();
        $messages = [];
        $messages[] = "Number of routes defined: " . count($routes);
        $messages[] = "";

        foreach ($routes as $route) {
            $messages[] = str_repeat("-", 80);
            $messages[] = " ";

            $messages[] =
                "Name: " . Console::coloredString($route->getName(), 'green');
            $messages[] =
                " Methods: " .
                str_pad(implode('|', $route->getMethod()), 20, ' ') .
                " | Path: " .
                $route->getPath();
            $messages[] = " Parameters:";
            $parameters = $route->getParameters();
            if (count($parameters) === 0) {
                $messages[] = "     None";
            } else {
                foreach ($parameters as $paramName => $paramPattern) {
                    $messages[] = "     - " . $paramName . ": " . $paramPattern;
                }
            }

            $messages[] = " Target: " . implode('::', $route->getTarget());
        }

        echo implode("\n", $messages);

        return 0;
    }

    /**
     * Get routes defined in configuration
     *
     * @return array
     */
    protected function getRoutes(): array
    {
        return Suricate::Router()->getRoutes();
    }
}
