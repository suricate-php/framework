<?php

declare(strict_types=1);

namespace Suricate;

use ArrayAccess;

class Container implements ArrayAccess
{
    protected $content;

    public function __construct(array $values = [])
    {
        $this->content = $values;
    }

    /**
     * ArrayAccess offsetExists implementation
     *
     * @param mixed $offset offset to check
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return isset($this->content[$offset]);
    }

    /**
     * ArrayAccess offsetGet implementation
     *
     * @param mixed $offset offset to get
     * @return mixed
     */
    public function offsetGet($offset)
    {
        if (isset($this->content[$offset])) {
            return $this->content[$offset];
        }

        return null;
    }

    /**
     * ArrayAccess offsetSet implementation
     *
     * @param mixed $offset offset to set
     * @param mixed $value  value to set
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->content[$offset] = $value;
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
}
