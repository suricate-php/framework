<?php declare(strict_types=1);
namespace Suricate;

class Container implements \ArrayAccess
{
    private $content;
    private $warehouse = [];

    public function __construct(array $values = [])
    {
        $this->content = $values;
    }

    /**
     * \ArrayAccess offsetExists implementation
     *
     * @param mixed $offset offset to check
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->content[$offset]);
    }

    /**
     * \ArrayAccess offsetGet implementation
     *
     * @param mixed $offset offset to get
     * @throws \InvalidArgumentException
     * @return bool
     */
    public function offsetGet($offset)
    {
        if (isset($this->content[$offset])) {
            return $this->content[$offset];
        }

        // Instantiate from warehouse if available
        if (isset($this->warehouse[$offset])) {
            $this->content[$offset] = new $this->warehouse[$offset]();
            return $this->content[$offset];
        }

        throw new \InvalidArgumentException('Unknown service ' . $offset);
    }

    /**
     * \ArrayAccess offsetSet implementation
     *
     * @param mixed $offset offset to set
     * @param mixed $value  value to set
     * @return void
     */
    public function offsetSet($offset, $value)
    {

    }

    /**
     * \ArrayAccess offsetUnset implementation
     *
     * @param mixed $offset offset to unset
     * @return void
     */
    public function offsetUnset($offset)
    {
        if (isset($this->content[$offset])) {
            unset($this->content[$offset]);
        }
    }

    /**
     * Set warehouse array
     *
     * @param array $serviceList warehouse content
     * @return Container
     */
    public function setWarehouse(array $serviceList)
    {
        $this->warehouse = $serviceList;

        return $this;
    }
}
