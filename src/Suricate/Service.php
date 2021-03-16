<?php

declare(strict_types=1);

namespace Suricate;

use InvalidArgumentException;

class Service implements Interfaces\IService
{
    protected $parametersList = [];
    protected $parametersValues = [];

    public function __construct()
    {
        // Service constructor
    }

    /**
     * Service getter
     *
     * @param string $variable
     * @return void
     * @throws InvalidArgumentException
     */
    public function __get($variable)
    {
        if (in_array($variable, $this->parametersList)) {
            if (isset($this->parametersValues[$variable])) {
                return $this->parametersValues[$variable];
            }

            return null;
        }

        throw new InvalidArgumentException(
            "Unknown configuration property " .
                get_called_class() .
                '->' .
                $variable
        );
    }

    /**
     * Service setter
     *
     * @param string $variable
     * @param mixed $value
     * @throws InvalidArgumentException
     */
    public function __set($variable, $value)
    {
        if (in_array($variable, $this->parametersList)) {
            $this->parametersValues[$variable] = $value;

            return $this;
        }

        throw new InvalidArgumentException(
            "Unknown configuration property " .
                get_called_class() .
                '->' .
                $variable
        );
    }

    /**
     * Service configurator
     *
     * @param array $parameters
     * @return void
     */
    public function configure($parameters = [])
    {
        foreach ($parameters as $key => $value) {
            $this->$key = $value;
        }
    }

    public function getParameter($parameter)
    {
        return $this->$parameter;
    }
}
