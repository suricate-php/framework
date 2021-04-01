<?php

declare(strict_types=1);

namespace Suricate;

use InvalidArgumentException;

class ServiceContainer extends Container
{
    /**
     * @var array $warehouse Services warehouse
     */
    private $warehouse = [];

    /**
     * Set warehouse array
     *
     * @param array $serviceList warehouse content
     * @return ServiceContainer
     */
    public function setWarehouse(array $serviceList)
    {
        $this->warehouse = $serviceList;

        return $this;
    }

    public function addToWarehouse(string $serviceName, string $serviceClass)
    {
        $this->warehouse[$serviceName] = $serviceClass;
    }

    public function offsetExists($offset): bool
    {
        return isset($this->warehouse[$offset]);
    }

    /**
     * ArrayAccess offsetGet implementation
     *
     * @param mixed $offset offset to get
     * @throws InvalidArgumentException
     * @return mixed
     */
    public function offsetGet($offset)
    {
        // Service has already been inited, returning it
        if (isset($this->content[$offset])) {
            return $this->content[$offset];
        }

        // Instantiate a new copy from warehouse if available
        if (isset($this->warehouse[$offset])) {
            $this->content[$offset] = new $this->warehouse[$offset]();
            return $this->content[$offset];
        }

        throw new InvalidArgumentException('Unknown service ' . $offset);
    }
}
