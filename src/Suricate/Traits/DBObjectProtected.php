<?php declare(strict_types=1);
namespace Suricate\Traits;

use Suricate\DBObject;

trait DBObjectProtected
{
    protected $protectedVariables           = [];
    protected $protectedValues              = [];
    protected $loadedProtectedVariables     = [];

    /**
     * @param string $name
     */
    private function getProtectedVariable($name)
    {
        // Variable exists, and is already loaded
        if (isset($this->protectedValues[$name]) && $this->isProtectedVariableLoaded($name)) {
            return $this->protectedValues[$name];
        }
        // Variable has not been loaded
        if (!$this->isProtectedVariableLoaded($name)) {
            if ($this->accessToProtectedVariable($name)) {
                $this->markProtectedVariableAsLoaded($name);
            }
        }

        if (isset($this->protectedValues[$name])) {
            return $this->protectedValues[$name];
        }

        return null;
    }
    /**
     * Mark a protected variable as loaded
     * @param  string $name varialbe name
     *
     * @return DBObject
     */
    public function markProtectedVariableAsLoaded($name)
    {
        if ($this->isProtectedVariable($name)) {
            $this->loadedProtectedVariables[$name] = true;
        }

        return $this;
    }

    /**
    * Check if variable is a protected variable
    * @param  string  $name variable name
    * @return boolean
    */
    public function isProtectedVariable($name)
    {
        return in_array($name, $this->protectedVariables);
    }

    /**
     * Check if a protected variable already have been loaded
     * @param  string  $name Variable name
     * @return boolean
     */
    protected function isProtectedVariableLoaded($name)
    {
        return isset($this->loadedProtectedVariables[$name]);
    }

    protected function accessToProtectedVariable($name)
    {
        return false;
    }
}