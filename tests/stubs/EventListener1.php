<?php

class EventListener1 extends \Suricate\Event\EventListener
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
        echo "payload for listerner1 is : " . $this->payload . "\n";
    }
}
