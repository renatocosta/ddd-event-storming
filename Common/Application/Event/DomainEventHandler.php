<?php

namespace Common\Application\Event;

interface DomainEventHandler
{
    /**
     * Handling an event and then dispatcher to another aggregate root
     * @param AbstractEvent $domainEvent
     */
    public function handle(AbstractEvent $domainEvent): void;

    /**
     * Check if an event is subscribed in one event handler
     * @param AbstractEvent $domainEvent
     * @return bool
     */
    public function isSubscribedTo(AbstractEvent $domainEvent): bool;

}