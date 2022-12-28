<?php

namespace Infrastructure\Integration\Events;

use Carbon\Carbon;
use Common\Application\Event\AbstractEvent;
use Common\Infrastructure\Event\IntegrationEventHandler;
use Domain\Model\Order\Events\OrderConfirmed;
use Domain\Model\Order\Events\OrderCreated;
use Domain\Model\ProjectReports\Events\ProjectReportsStatusChanged;
use Domain\Model\ProjectReports\ProjectReportsStatus;
use Illuminate\Support\Str;
use Infrastructure\Repositories\OutboxIntegrationEventsRepository;

final class CloseCrmEventHandler implements IntegrationEventHandler
{

    private array $customFields = [];

    private const PROJECT_STATUS = [ProjectReportsStatus::ORDER_ACCEPTED, ProjectReportsStatus::PROJECT_STARTED, ProjectReportsStatus::PROJECT_FINISHED, ProjectReportsStatus::PROJECT_CANCELLED, ProjectReportsStatus::PROJECT_CLEANER_UPDATED_THE_CLEANING];

    private array $payload = [];

    public function __construct(private OutboxIntegrationEventsRepository $outboxIntegrationEventsRepository)
    {

        $this->customFields = [
            'OrderCreated' => ['followup_date' => env('CLOSE_ORDER_CREATED_FIELD'), 'payload_field_key' => null, 'send_activity' => true, 'activity_message' => 'Renter: %s created a DDD order for Property address: %s.'],
            'OrderConfirmed' => ['followup_date' => env('CLOSE_ORDER_CONFIRMED_FIELD'), 'payload_field_key' => null, 'send_activity' => false],
            'OrderAccepted' => ['followup_date' => env('CLOSE_CLEANING_SCHEDULED_FIELD'), 'payload_field_key' => 'start_date', 'send_activity' => true, 'activity_message' => 'Cleaner %s accepted a DDD cleaning request for Property address: %s.'],
            'ProjectStarted' => ['followup_date' => env('CLOSE_CLEANING_STARTED_FIELD'), 'payload_field_key' => 'started_at', 'send_activity' => false],
            'ProjectFinished' => ['followup_date' => env('CLOSE_CLEANING_COMPLETED_FIELD'), 'payload_field_key' => 'finished_at', 'send_activity' => true, 'activity_message' => '🎉 Completed DDD cleaning project by %s for Renter: %s at Property address: %s.'],
            'ProjectCancelled' => ['send_activity' => true],
            'CleanerUpdatedTheCleaning' => ['followup_date' => env('CLOSE_CLEANING_RESCHEDULED'), 'payload_field_key' => 'start_date', 'send_activity' => true, 'activity_message' => 'Cleaner updated the Cleaning date %s.']
        ];
    }

    public function isSubscribedTo(AbstractEvent $domainEvent): bool
    {

        if ($domainEvent instanceof ProjectReportsStatusChanged) {
            $data = $domainEvent->entity->payload()->asArray();
            return in_array($data['event_type'], self::PROJECT_STATUS);
        }

        return $domainEvent instanceof OrderCreated || $domainEvent instanceof OrderConfirmed;
    }

    public function handle(AbstractEvent $domainEvent): void
    {

        $data = $domainEvent->entity->payload()->asArray();

        $eventData = $data['event_data'];

        $customFields = $this->customFields[$data['event_type']];

        if (isset($customFields['followup_date']) && isset($eventData['project'][$customFields['payload_field_key']])) {
            $eventDate = Carbon::createFromTimestamp($eventData['project'][$customFields['payload_field_key']], $eventData['customer']['location']['timezone']);
        } else {
            $eventDate = Carbon::createFromTimestamp(strtotime('now'), $eventData['customer']['location']['timezone']);
        }

        $timeZone = Str::substr($eventDate->getOffsetString(), 0, 3);

        $this->payload = [];

        if (isset($customFields['followup_date'])) {
            $this->payload[$customFields['followup_date']] = $eventDate->format('Y-m-d H:i');
        }

        //Send default data at once
        if ($data['event_type'] == 'OrderCreated') {
            $this->payload = array_merge($this->payload, [
                env('CLOSE_USER_ID_FIELD') => $eventData['customer']['user_id'],
                'name' => $eventData['customer']['personal_information']['name'],
                'url'  => '',
                env('CLOSE_USER_TYPE_FIELD') => 'ddd renter',
                'contacts' => [
                    ['emails' => [['email' => $eventData['customer']['personal_information']['email']]]],
                    ['phones' => [['phone' => '+' . $eventData['customer']['personal_information']['phone_number']]]]
                ],
                'addresses' => [
                    [
                        'address_1' => $eventData['customer']['property']['address'],
                        'city' => $eventData['customer']['property']['city'],
                        'state' => $eventData['customer']['property']['state'],
                        'zipcode' => $eventData['customer']['property']['zipcode'],
                        'country' => $eventData['customer']['personal_information']['country_code'],
                    ]
                ]
            ]);
        }

        if (in_array($data['event_type'], self::PROJECT_STATUS) && $data['event_type'] != ProjectReportsStatus::PROJECT_CANCELLED && $data['event_type'] != ProjectReportsStatus::PROJECT_CLEANER_UPDATED_THE_CLEANING) {
            $this->payload['cleaner']['name'] = $eventData['cleaner']['name'];
        }

        $this->payload['event_type'] = $data['event_type'];
        $this->payload['user_id'] = $eventData['customer']['user_id'];

        $this->addExtraFields($eventData);

        $this->payload['timezone'] = $timeZone;

        if ($this->hasActivity($data['event_type'])) {
            $this->addActivityMessage($customFields, $eventData);
        }

        $this->outboxIntegrationEventsRepository->create(['type' => $domainEvent->getEventName(), 'destination' => 'close', 'data' => json_encode($this->payload), 'messageable_id' => $domainEvent->entity->getIdentifier()->id]);
    }

    private function hasActivity(string $eventType): bool
    {
        return in_array($eventType, ['OrderCreated', 'OrderAccepted', 'ProjectFinished', 'ProjectCancelled', 'CleanerUpdatedTheCleaning']);
    }

    private function addExtraFields(array $eventData): void
    {
        switch ($this->payload['event_type']) {
            case 'OrderAccepted':
                $this->payload[env('CLOSE_CLEANING_ACCEPTED_FIELD')] = Carbon::createFromTimestamp(strtotime('now'), $eventData['customer']['location']['timezone']);
                break;
            case 'ProjectCancelled':
                $this->payload[env('CLOSE_CLEANING_CANCELED_TYPE_FIELD')] = ProjectReportsStatus::CANCELLATION_REASONS[$eventData['project']['cancellation_reason']];
                break;
        }
    }

    private function addActivityMessage(array $customFields, array $eventData): void
    {
        switch ($this->payload['event_type']) {
            case 'OrderCreated':
                $this->payload['activity_message'] = sprintf($customFields['activity_message'], $this->payload['name'], $eventData['customer']['property']['address']);
                break;
            case 'OrderAccepted':
                $this->payload['activity_message'] = sprintf($customFields['activity_message'], $eventData['cleaner']['name'], $eventData['customer']['property']['address']);
                break;
            case 'ProjectFinished':
                $this->payload['activity_message'] = sprintf($customFields['activity_message'], $eventData['cleaner']['name'], $eventData['customer']['personal_information']['name'], $eventData['customer']['property']['address']);
                break;
            case 'ProjectCancelled':
                $this->payload['activity_message'] =  ProjectReportsStatus::CANCELLATION_REASONS[$eventData['project']['cancellation_reason']];
                break;
            case 'CleanerUpdatedTheCleaning':
                $this->payload['activity_message'] = sprintf($customFields['activity_message'], $this->payload[$customFields['followup_date']]);
                break;
        }
    }
}
