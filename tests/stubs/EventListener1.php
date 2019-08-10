<?php

class EventListener1
{
    protected $payload;

    public function __construct($payload)
    {
        $this->payload = $payload;
    }

    public function handle()
    {
        echo "payload for listerner1 is : " . $this->payload . "\n";
    }
}
