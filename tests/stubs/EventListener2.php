<?php

class EventListener2 extends \Suricate\Event\EventListener
{
    protected $payload;

    public function __construct($payload)
    {
        $this->payload = $payload;
    }

    public function handle()
    {
        echo "payload for listerner2 is : " . $this->payload . "\n";

        // stop propagation
        return false;
    }
}
