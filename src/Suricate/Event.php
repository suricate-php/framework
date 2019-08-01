<?php

declare(strict_types=1);

namespace Suricate;

class Event
{
    protected $listeners = [];
    protected $wildcardListeners = [];

    private $sortedListeners = [];

    public function listen($events, $listener, $priority = 0)
    {
        foreach ((array) $events as $event) {
            if (strpos($event, '*') !== false) {
            } else {
                $this->listeners[$event][$priority][] = $listener;
            }
        }
    }

    public function fire($event, $eventData = [])
    {
        $impactedListeners = $this->getImpactedListeners($event);
        foreach ($impactedListeners as $listener) {
        }
    }

    public function subscribe($subscriber)
    {
        $subscriber->subscribe($this);

        return $this;
    }

    protected function getImpactedListeners($eventName)
    {
        if (!isset($this->sortedListeners[$eventName])) {
            $listeners = isset($this->listeners[$eventName])
                ? $this->listeners[$eventName]
                : [];
            $listeners = array_merge(
                $listeners,
                $this->getImpactedWildcardListeners($listeners)
            );

            $listeners = krsort($listeners);
            $this->sortedListeners[$eventName] = $listeners;
        }

        return $this->sortedListeners[$eventName];
    }

    protected function getImpactedWildcardListeners($events)
    {
        $listeners = [];

        return $listeners;
    }
}
