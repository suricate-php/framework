<?php

class EventListener2 extends \Suricate\Event\EventListener
{
    protected $payload;
    protected string $eventType;

    public function __construct($payload, string $eventType)
    {
        $this->payload = $payload;
        $this->eventType = $eventType;
    }

    public function handle()
    {
        echo "payload for listerner2 is : " . $this->payload . "\n";

        // stop propagation
        return false;
    }
}
