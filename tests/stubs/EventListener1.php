<?php
declare(strict_types=1);
class EventListener1 extends \Suricate\Event\EventListener
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
