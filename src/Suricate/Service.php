<?php
namespace Suricate;

use InvalidArgumentException;

class Service implements Interfaces\IService
{
    protected $parametersList   = array();
    protected $parametersValues = array();

    public function __construct()
    {
        // Service constructor
    }

    public function __get($variable)
    {
        if (in_array($variable, $this->parametersList)) {
            if (isset($this->parametersValues[$variable])) {
                return $this->parametersValues[$variable];
            } else {
                return null;
            }
        } else {
            throw new InvalidArgumentException("Unknown configuration property " . get_called_class() . '->' .$variable);
        }
    }

    public function __set($variable, $value)
    {
        if (in_array($variable, $this->parametersList)) {
            $this->parametersValues[$variable] = $value;

            return $this;
        } else {
            throw new InvalidArgumentException("Unknown configuration property " . get_called_class() . '->' . $variable);
        }
    }

    public function configure($parameters = array())
    {
        foreach ($parameters as $key => $value) {
            if (in_array($key, $this->parametersList)) {
                $this->$key = $value;
            } else {
                throw new InvalidArgumentException("Unknown configuration property : " . get_called_class() . "->" . $key);
            }
        }

        $this->init();
    }

    public function getParameter($parameter)
    {
        if (in_array($parameter, $this->parametersList)) {
            return isset($this->parametersValues[$parameter]) ? $this->parametersValues[$parameter] : null;
        } else {
            throw new InvalidArgumentException("Unknown configuration property : " . get_called_class() . "->" . $parameter);
        }
    }

    protected function init()
    {
        
    }
}
