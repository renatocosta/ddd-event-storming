<?php

namespace Infrastructure\Integration\Events;

use Carbon\Carbon;
use Common\Application\Event\AbstractEvent;
use Common\Infrastructure\Event\IntegrationEventHandler;
use Domain\Model\Order\Events\OrderConfirmed;
use Domain\Model\Order\OrderStatus;
use Domain\Model\ProjectReports\Events\ProjectReportsStatusChanged;
use Domain\Model\ProjectReports\ProjectReportsStatus;
use Infrastructure\Repositories\OutboxIntegrationEventsRepository;

final class SlackEventHandler implements IntegrationEventHandler
{

    public const PROJECT_STATUS = [ProjectReportsStatus::ORDER_ACCEPTED, ProjectReportsStatus::PROJECT_STARTED, ProjectReportsStatus::PROJECT_FINISHED, ProjectReportsStatus::PROJECT_CANCELLED];

    private const WORKSPACE_STATUS_MESSAGE = [
        ProjectReportsStatus::ORDER_ACCEPTED => 'The cleaner %s accepted the request', ProjectReportsStatus::PROJECT_STARTED => 'The cleaner %s has started the cleaning', ProjectReportsStatus::PROJECT_FINISHED => 'The cleaner %s finished cleaning the property', ProjectReportsStatus::PROJECT_CANCELLED => [
            'reasons' => ProjectReportsStatus::CANCELLATION_REASONS
        ]
    ];

    public function __construct(private OutboxIntegrationEventsRepository $outboxIntegrationEventsRepository)
    {
    }

    public function isSubscribedTo(AbstractEvent $domainEvent): bool
    {
        if ($domainEvent instanceof ProjectReportsStatusChanged) {
            $data = $domainEvent->entity->payload()->asArray();
            return in_array($data['event_type'], self::PROJECT_STATUS);
        }

        return $domainEvent instanceof OrderConfirmed;
    }

    public function handle(AbstractEvent $domainEvent): void
    {
        $payload = $domainEvent->entity->payload()->asArray();
        $parameters = [];

        $backofficeTb = env('EXTERNALPARTNER_API') . 'nova/resources/accounts/';
        $orderInfo = $payload['event_data'];

        if (in_array($payload['event_type'], self::PROJECT_STATUS)) {
            if ($payload['event_type'] == ProjectReportsStatus::PROJECT_CANCELLED) {
                $reasons = self::WORKSPACE_STATUS_MESSAGE[ProjectReportsStatus::PROJECT_CANCELLED]['reasons'];
                $message = $reasons[$payload['event_data']['project']['cancellation_reason']];
                $followUpDateTitle = 'Cancellation Date';
                $followUpDate = $orderInfo['project']['cancellation_date'];
            } else {
                $followUpDateTitle = 'Cleaning Date';
                $followUpDate = $orderInfo['project']['start_date'];
                $message = $payload['event_data']['cleaner']['name'] . ' ' . $backofficeTb . $payload['event_data']['cleaner']['id'];
                $message =  sprintf(self::WORKSPACE_STATUS_MESSAGE[$payload['event_type']], $message);
            }
        } else if ($payload['event_type'] == 'OrderConfirmed') {
            $followUpDateTitle = 'Cleaning Date';
            $followUpDate = $orderInfo['project']['start_date'];
            $cleaners = implode(" | ", array_map(function ($cleaner) use ($backofficeTb) {
                return $cleaner['name'] . ' ' . $backofficeTb . $cleaner['id'];
            }, $orderInfo['cleaners']));
            $message = sprintf(OrderStatus::CONFIRMED_MESSAGE, $cleaners);
        }

        $parameters = ['event_type' => $payload['event_type'], 'customer' => $orderInfo['customer']['personal_information']['name'], 'message' => $message, 'location' => $orderInfo['customer']['property']['address'], 'followup_date_title' =>  $followUpDateTitle, 'followup_date' => Carbon::createFromTimestamp($followUpDate, $orderInfo['customer']['location']['timezone'])->format('Y-m-d H:i') . ' ' . $orderInfo['customer']['location']['timezone'], 'time' => Carbon::now($orderInfo['customer']['location']['timezone'])->format('Y-m-d H:i') . ' ' . $orderInfo['customer']['location']['timezone'], 'orderId' => $domainEvent->entity->getIdentifier()->id];

        $this->outboxIntegrationEventsRepository->create(['type' => $domainEvent->getEventName(), 'destination' => 'workspace', 'data' => json_encode($parameters), 'messageable_id' => $domainEvent->entity->getIdentifier()->id]);
    }
}
