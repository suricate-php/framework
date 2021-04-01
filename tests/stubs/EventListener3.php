<?php
declare(strict_types=1);
class EventListener3 extends \Suricate\Event\EventListener
{
    protected $payload;

    public function __construct($payload)
    {
        $this->payload = $payload;
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
