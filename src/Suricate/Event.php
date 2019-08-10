<?php

declare(strict_types=1);

namespace Suricate;

class Event
{
    /** @const string event type*/
    const EVENT_TYPE = '';

    /** @var mixed $payload Event payload */
    protected $payload;
    /**
     * Get event type
     *
     * @return string
     */
    public static function getEventType(): string
    {
        return static::EVENT_TYPE;
    }

    public function __construct($payload = null)
    {
        $this->payload = $payload;
    }
}
