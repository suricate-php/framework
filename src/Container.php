<?php
namespace Fwk;

class Container implements \ArrayAccess
{
    private $content;
    private $warehouse;

    public function __construct(array $values = array())
    {
        $this->content = $values;
    }

    public function offsetExists($offset)
    {
        return isset($this->content[$offset]);
    }

    public function offsetGet($offset)
    {
        if (isset($this->content[$offset])) {
            return $this->content[$offset];
        } else {
            if (isset($this->warehouse[$offset])) {
                $this->content[$offset] = new $this->warehouse[$offset]();
                return $this->content[$offset];
            } else {
                throw new \InvalidArgumentException('Unknown service ' . $offset);
            }
            
        }
    }

    public function offsetSet($offset, $value)
    {

    }

    public function offsetUnset($offset)
    {
        if (isset($this->content[$offset])) {
            unset($this->content[$offset]);
        }
    }

    public function setWarehouse($serviceList)
    {
        $this->warehouse = $serviceList;

        return $this;
    }
    /*
    public function getServiceNameList()
    {
        return array_keys($this->content);
    }
    */
}
