<?php

declare(strict_types=1);

namespace Suricate\Event;

use Suricate\Suricate;
use InvalidArgumentException;

class EventDispatcher extends \Suricate\Service
{
    /**
     * Array of declared listeners
     *
     * @var array
     */
    protected $listeners = [];

    /**
     * Array of sorted listeners (flattened from listeners)
     *
     * @var array
     */
    protected $sortedListeners = [];

    /**
     * Configure EventDispatcher
     *
     * @param array $parameters
     * @return void
     */
    public function configure($parameters = [])
    {
        foreach ($parameters as $eventListenerData) {
            if (
                isset($eventListenerData['event']) &&
                isset($eventListenerData['listeners'])
            ) {
                foreach ((array) $eventListenerData['listeners'] as $listener) {
                    $listenerDefinition = explode('|', $listener);
                    $listenerName = $listenerDefinition[0];
                    $listenerPriority = isset($listenerDefinition[1])
                        ? (int) $listenerDefinition[1]
                        : 0;
                    $this->addListener(
                        $eventListenerData['event'],
                        $listenerName,
                        $listenerPriority
                    );
                }
            }
        }
    }

    /**
     * Declare event listened to
     *
     * @param string $event    One or more event to listen to
     * @param string $listener Listener class name
     * @param int    $priority Listener priority, 0 is high priority
     *
     * @return void
     */
    public function addListener($event, string $listener, int $priority = 0)
    {
        $eventType = is_subclass_of($event, '\Suricate\Event\Event')
            ? $event::getEventType()
            : $event;

        $this->listeners[$eventType][$priority][] = $listener;

        unset($this->sortedListeners[$eventType]);
    }

    /**
     * Dispatch an event
     *
     * @param string|\Suricate\Event\Event $event   Event to be dispatched
     * @param mixed                        $payload Optionnal event payload
     * @return void
     */
    public function fire($event, $payload = null)
    {
        $eventType = null;
        $eventPayload = null;

        if (
            is_object($event) &&
            is_subclass_of($event, '\Suricate\Event\Event')
        ) {
            $eventType = $event::getEventType();
            $eventPayload = $event;
        }
        if (is_string($event)) {
            $eventType = $event;
            $eventPayload = $payload;
        }

        if ($eventType === null) {
            throw new InvalidArgumentException(
                'Event type is not a string nor Event subclass'
            );
        }

        $impactedListeners = $this->getImpactedListeners($eventType);

        foreach ($impactedListeners as $listener) {
            Suricate::Logger()->debug(
                sprintf(
                    '[event] Passing event type: "%s" to "%s"',
                    $eventType,
                    $listener
                )
            );
            
            $result = with(new $listener($eventPayload, $eventType))->handle();
            if ($result === false) {
                Suricate::Logger()->debug(
                    sprintf(
                        '[event] stop propagation of event "%s"',
                        $eventType
                    )
                );
                break;
            }
        }
    }

    /**
     * Get list of eligible listeners for an event
     *
     * @param string $eventType
     * @return array
     */
    protected function getImpactedListeners(string $eventType): array
    {
        if (isset($this->sortedListeners[$eventType])) {
            return $this->sortedListeners[$eventType];
        }
        $this->sortListeners($eventType);

        return $this->sortedListeners[$eventType] ?? [];
    }

    /**
     * Sort and flatten listeners list according ot priority
     *
     * @param string $eventType event type
     * @return void
     */
    protected function sortListeners(string $eventType)
    {
        if (isset($this->listeners[$eventType])) {
            $listeners = $this->listeners[$eventType];
            ksort($listeners);
            $this->sortedListeners[$eventType] = flatten($listeners);
        }
    }
}
