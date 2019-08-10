<?php

class TestEvent extends \Suricate\Event\Event
{
    const EVENT_TYPE = 'my.test.event';

    public function getEventContent()
    {
        return 'lorem ipsum';
    }
}
