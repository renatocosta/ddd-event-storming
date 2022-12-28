<?php

namespace Infrastructure\Integration\Events;

use Common\Application\Event\AbstractEvent;
use Common\Infrastructure\Event\IntegrationEventHandler;
use Domain\Model\ProjectReports\Events\ProjectReportsStatusChanged;
use Domain\Model\ProjectReports\ProjectReportsStatus;
use Infrastructure\Repositories\OutboxIntegrationEventsRepository;

final class MixPanelEventHandler implements IntegrationEventHandler
{

    public function __construct(private OutboxIntegrationEventsRepository $outboxIntegrationEventsRepository)
    {
    }

    public function isSubscribedTo(AbstractEvent $domainEvent): bool
    {

        if (!$domainEvent instanceof ProjectReportsStatusChanged) return false;

        $data = $domainEvent->entity->payload()->asArray();
        return in_array($data['event_type'], ProjectReportsStatus::TRACKING_STATUS);
    }

    public function handle(AbstractEvent $domainEvent): void
    {
        $entity = $domainEvent->entity;
        $eventType = $entity->payload()->asArray()['event_type'];
        $data = $entity->payload()->asArray();
        $userId = $data['event_data']['customer']['user_id'];
        $payload = ['data' => $data, 'user_id' => $userId, 'event_type' => $eventType];

        $this->outboxIntegrationEventsRepository->create(['type' => $domainEvent->getEventName(), 'destination' => 'business_analytics', 'data' => json_encode($payload), 'messageable_id' => $domainEvent->entity->getIdentifier()->id]);
    }
}
