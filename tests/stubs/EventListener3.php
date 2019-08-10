<?php

class EventListener3
{
    protected $payload;

    public function __construct($payload)
    {
        $this->payload = $payload;
    }

    public function handle()
    {
        echo "payload for listerner3 is : " .
            $this->payload->getEventContent() .
            "\n";

        // stop propagation
        return false;
    }
}
