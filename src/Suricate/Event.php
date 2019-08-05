<?php

declare(strict_types=1);

namespace Suricate;

class Event
{
    /**
     * @const string
     */
    const EVENT_TYPE = '';

    public static function getEventType(): string
    {
        return static::EVENT_TYPE;
    }
}
