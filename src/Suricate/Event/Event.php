<?php

declare(strict_types=1);

namespace Suricate\Event;

class Event
{
    /** @const string event type*/
    const EVENT_TYPE = '';

    /**
     * Get event type
     *
     * @return string
     */
    public static function getEventType(): string
    {
        return static::EVENT_TYPE;
    }
}
