<?php
namespace Suricate;

class Event
{
    protected $listeners            = array();
    protected $wildcardListeners    = array();

    private $sortedListeners        = array();

    public function listen($events, $listener, $priority = 0)
    {
        foreach ((array) $events as $event) {
            if (strpos($event, '*') !== false) {

            } else {
                $this->listeners[$event][$priority][] = $listener;
            }
        }
    }

    public function fire($event, $eventData = array())
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
            $listeners = isset($this->listeners[$eventName]) ? $this->listeners[$eventName] : array();
            $listeners = array_merge($listeners, $this->getImpactedWildcardListeners($listeners));

            $listeners = krsort($listeners);
            $this->sortedListeners[$eventName] = $listeners;
        }

        return $this->sortedListeners[$eventName];
    }

    protected function getImpactedWildcardListeners($events)
    {
        $listeners = array();

        return $listeners;
    }
}
