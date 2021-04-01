<?php
declare(strict_types=1);

use Suricate\Event\Event;

class TestEvent extends Event
{
    const EVENT_TYPE = 'my.test.event';

    public function getEventContent()
    {
        return 'lorem ipsum';
    }
}
