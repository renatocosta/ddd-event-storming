<?php

namespace Common\Application\Event;

abstract class AbstractEvent implements Eventable
{

    /**
     * @var string
     */
    private $eventName;

    /**
     * @var \DateTimeImmutable
     */
    private $createdAt;

    public function __construct(public object $domainEvent)
    {
        $this->setEventName();
        $this->createdAt = new \DateTimeImmutable();
    }

    private function setEventName(): void
    {
        $this->eventName = get_class($this);
    }

    public function getEventName(): string
    {
        return $this->eventName;
    }

    public function trackEvent(): string
    {
        return method_exists($this->domainEvent, 'getIdentifier') && $this->domainEvent->getIdentifier() != null ? $this->getEventName() . ' - ' . $this->domainEvent->getIdentifier()->id : $this->getEventName();
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
