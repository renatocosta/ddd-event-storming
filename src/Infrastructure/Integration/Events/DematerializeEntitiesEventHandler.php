<?php

namespace Infrastructure\Integration\Events;

use Common\Application\Event\AbstractEvent;
use Common\Infrastructure\Event\IntegrationEventHandler;
use Domain\Model\Order\Events\OrderCreated;
use Domain\Model\Order\Events\OrderUpdated;
use Domain\Model\ProjectReports\Events\ProjectReportsStatusChanged;
use Domain\Model\ProjectReports\ProjectReportsStatus;
use Infrastructure\Repositories\OutboxIntegrationEventsRepository;

final class DematerializeEntitiesEventHandler implements IntegrationEventHandler
{

    public function __construct(private OutboxIntegrationEventsRepository $outboxIntegrationEventsRepository)
    {
    }

    public function isSubscribedTo(AbstractEvent $domainEvent): bool
    {
        if ($domainEvent instanceof ProjectReportsStatusChanged) {
            $data = $domainEvent->entity->payload()->asArray();
            return $data['event_type'] == ProjectReportsStatus::ORDER_ACCEPTED || $data['event_type'] == ProjectReportsStatus::PROJECT_FINISHED;
        }

        return $domainEvent instanceof OrderCreated || $domainEvent instanceof OrderUpdated;
    }

    public function handle(AbstractEvent $domainEvent): void
    {
        $entity = $domainEvent->entity;
        $eventType = $entity->payload()->asArray()['event_type'];
        $data = $entity->payload()->asArray();
        $payload = ['data' => $data, 'event_type' => $eventType, 'order_id' => $domainEvent->entity->getIdentifier()->id];

        $this->outboxIntegrationEventsRepository->create(['type' => $domainEvent->getEventName(), 'destination' => 'general_purpose', 'data' => json_encode($payload), 'messageable_id' => $domainEvent->entity->getIdentifier()->id]);
    }
}
