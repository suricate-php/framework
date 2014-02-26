<?php
namespace Fwk;

class Route
{
    private $url;
    //private $HTTPMethod;
    private $parametersDefinitions;
    public $parametersValues;

    public $isMatched;
    public $target;
    
    const PARAMETER_TYPE_ALPHA  = '[\d\w\s_-]+';
    const PARAMETER_TYPE_NUMBER = '\d+';

    const ROUTE_DEFAULT_CONTROLLER  = 'home';
    const ROUTE_DEFAULT_METHOD      = 'index';

    public function __construct($url, $requestUri, $routeTarget, $parametersDefinitions = array())
    {
        $this->isMatched                = false;
        $this->url                      = $url;
        $this->parametersDefinitions    = $parametersDefinitions;
        $this->parametersValues         = array();

        if (($pos = strpos($requestUri, '?')) !== false) {
            $requestUri = substr($requestUri, 0, $pos);
        }

        // Get all route parameters
        preg_match_all('|:([\w]+)|', $url, $routeParameters);
        $routeParametersNames = $routeParameters[1];
        
        $patternMatching = $this->url;

        // Patterns parameters are not set, considering implicit declaration
        
        foreach ($routeParametersNames as $parameter) {
            if (!isset($this->parametersDefinitions[$parameter])) {
                $this->parametersDefinitions[$parameter] = '.*';
            }
        }
        
        /**
        // TODO : no parameters ?
        **/
        
        // Assigning parameters
        foreach ($this->parametersDefinitions as $parameterName => $parameterDefinition) {
            $patternMatching = str_replace(':' . $parameterName, '(?<' . $parameterName . '>' . $parameterDefinition . ')', $patternMatching);
        }
    
        /**
         TODO : match protocol
         */
        
        // requestUri is matching pattern, set as matched route
        if (preg_match('#^' . $patternMatching . '$#', $requestUri, $matching)) {

            foreach ($routeParametersNames as $currentParameter) {
                $this->parametersValues[$currentParameter] = isset($matching[$currentParameter]) ? $matching[$currentParameter] : null;
            }

            $this->target           = $routeTarget;
            $this->isMatched        = true;
        }
    }
}
