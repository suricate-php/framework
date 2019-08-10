<?php

declare(strict_types=1);

namespace Suricate\Event;

abstract class EventListener
{
    protected $payload;

    public function __construct($payload)
    {
        $this->payload = $payload;
    }

    /**
     * Event listener handle method
     *
     * @return boolean|void returning false stop event propagation
     */
    abstract public function handle();
}
