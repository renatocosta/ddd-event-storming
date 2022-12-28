<?php

namespace Common\Application\Event\Bus;

use Common\Application\Event\AbstractEvent;
use Common\Application\Event\DomainEventHandler;
use Common\Infrastructure\Event\IntegrationEventHandler;
use Exception;
use Illuminate\Support\Facades\Log;
use SplDoublyLinkedList;

final class DomainEventBus
{

    private SplDoublyLinkedList $eventHandlers;

    private array $integrationEventHandlers = [];

    private array $raisedEvents = [];

    private ?string $startIntegrationHandlerAfter = null;

    public function __construct()
    {
        $this->reset();
    }

    public function __clone()
    {
        throw new \BadMethodCallException('Clone is not supported');
    }

    public function subscribers(DomainEventHandler ...$subscribers): void
    {
        if (count($subscribers) == 0) throw new Exception('At least one subscribed required');

        foreach ($subscribers as $subscriber) {
            $this->subscribe($subscriber);
        }
    }

    public function subscribe(DomainEventHandler $aDomainEventHandler): void
    {
        $this->eventHandlers->push($aDomainEventHandler);
    }

    public function publish(AbstractEvent $aDomainEvent): void
    {

        for ($this->eventHandlers->rewind(); $this->eventHandlers->valid(); $this->eventHandlers->next()) {
            $eventHandler = $this->eventHandlers->current();

            if ($eventHandler->isSubscribedTo($aDomainEvent)) {

                //Add an integration event handler
                if ($eventHandler instanceof IntegrationEventHandler && !isset($this->integrationEventHandlers[get_class($eventHandler)])) {
                    $this->integrationEventHandlers[] = ['handler' => $eventHandler, 'event' => $aDomainEvent];
                    continue;
                }
                $this->raisedEvents = array_merge($this->raisedEvents, [$aDomainEvent->getEventName() => $aDomainEvent]);
                Log::channel('domain_events')->info($aDomainEvent->trackEvent());
                $eventHandler->handle($aDomainEvent);
            }
        }

        //Publish integration event handlers after running all domain event handlers
        if ($this->canIntregrationEventsBeDispatched($aDomainEvent->getEventName())) {
            foreach ($this->integrationEventHandlers as $integrationEventHandler) {
                Log::channel('domain_events')->info("integration event handler: " . $aDomainEvent->trackEvent() . " -- " . get_class($integrationEventHandler['handler']));
                $integrationEventHandler['handler']->handle($integrationEventHandler['event']);
            }
        }
    }

    public function startIntegrationHandlerAfter(string $startIntegrationHandlerAfter): void
    {
        $this->startIntegrationHandlerAfter = $startIntegrationHandlerAfter;
    }

    private function canIntregrationEventsBeDispatched(string $domainEvent): bool
    {
        return $domainEvent == $this->startIntegrationHandlerAfter;
    }

    public function reset(): void
    {
        $this->eventHandlers = new SplDoublyLinkedList();
    }

    public function getRaisedEvents(): array
    {
        return $this->raisedEvents;
    }
}
