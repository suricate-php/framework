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

    public function configure($parameters = [])
    {
        foreach ($parameters as $key => $value) {
            $this->$key = $value;
        }

        $this->init();
    }

    public function getParameter($parameter)
    {
        return $this->$parameter;
    }

    protected function init()
    {
    }
}
