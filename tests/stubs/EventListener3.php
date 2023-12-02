<?php

class EventListener3 extends \Suricate\Event\EventListener
{
    protected $payload;
    protected string $eventType;

    public function __construct($payload, string $eventType)
    {
        $this->payload = $payload;
        $this->eventType = $eventType;
    }

    public function handle(): ?bool
    {
        echo "payload for listerner3 is : " .
            $this->payload->getEventContent() .
            "\n";

        // stop propagation
        return false;
    }
}
