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
     */
    public function execute(): int
    {
        $routes = $this->getRoutes();
        echo "Number of routes defined: " . count($routes) . "\n\n";

        foreach ($routes as $route) {
            echo str_repeat("-", 80) . "\n";
            echo " ";
            echo "Name: " .
                Console::coloredString($route->getName(), 'green') .
                "\n";
            echo " Methods: " .
                str_pad(implode('|', $route->getMethod()), 20, ' ');
            echo " | Path: ";
            echo $route->getPath();
            echo "\n";
            echo " Parameters:\n";
            $parameters = $route->getParameters();
            if (count($parameters) === 0) {
                echo "     None\n";
            } else {
                foreach ($parameters as $paramName => $paramPattern) {
                    echo "     - " . $paramName . ": " . $paramPattern . "\n";
                }
            }

            echo " Target: " . implode('::', $route->getTarget()) . "\n";
        }

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
