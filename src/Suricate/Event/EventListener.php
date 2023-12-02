<?php

declare(strict_types=1);

namespace Suricate\Event;

abstract class EventListener
{
    protected $payload;
    protected string $eventType;

    public function __construct($payload, string $eventType)
    {
        $this->payload = $payload;
        $this->eventType = $eventType;
    }

    /**
     * Event listener handle method
     *
     * @return boolean|void returning false stop event propagation
     */
    abstract public function handle();
}
