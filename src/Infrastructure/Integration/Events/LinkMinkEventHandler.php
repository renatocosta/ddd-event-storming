<?php

namespace Infrastructure\Integration\Events;

use Common\Application\Event\AbstractEvent;
use Common\Infrastructure\Event\IntegrationEventHandler;
use Domain\Model\ProjectReports\Events\ProjectReportsStatusChanged;
use Domain\Model\ProjectReports\ProjectReportsStatus;
use Illuminate\Support\Str;
use Infrastructure\Repositories\OutboxIntegrationEventsRepository;

final class LinkMinkEventHandler implements IntegrationEventHandler
{

    private array $data = [];

    public function __construct(private OutboxIntegrationEventsRepository $outboxIntegrationEventsRepository)
    {
    }

    public function isSubscribedTo(AbstractEvent $domainEvent): bool
    {

        if (!$domainEvent instanceof ProjectReportsStatusChanged) return false;

        $this->data = $domainEvent->entity->payload()->asArray();

        return $this->data['event_data']['affiliate_trackable'] && $this->data['event_type'] == ProjectReportsStatus::PROJECT_PAYMENT_SUCCEEDED;
    }

    public function handle(AbstractEvent $domainEvent): void
    {

        $userId = $this->data['event_data']['customer']['user_id'];
        $payload = ['user_id' => $userId, 'order_id' => $domainEvent->entity->getIdentifier()->id, 'email' => $this->data['event_data']['customer']['personal_information']['email'], 'stripe_customer_id' => $this->data['event_data']['customer']['payment']['customer_token'], 'affiliate_trackable_ref' => $this->data['event_data']['affiliate_trackable'], 'amount' => Str::remove('.', $this->data['event_data']['customer']['payment']['amount']), 'currency' => Str::lower($this->data['event_data']['customer']['payment']['currency'])];

        $this->outboxIntegrationEventsRepository->create(['type' => $domainEvent->getEventName(), 'destination' => 'affiliate_conversions', 'data' => json_encode($payload), 'messageable_id' => $domainEvent->entity->getIdentifier()->id]);
    }
}
