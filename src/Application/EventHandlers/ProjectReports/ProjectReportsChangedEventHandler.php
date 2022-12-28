<?php

namespace Application\EventHandlers\ProjectReports;

use Common\Application\Event\AbstractEvent;
use Common\Application\Event\DomainEventHandler;
use Domain\Model\ProjectReports\Events\ProjectReportsStatusChanged;
use Domain\Model\ProjectReports\IProjectReportsRepository;
use Domain\Services\OrderTrackable;

final class ProjectReportsChangedEventHandler implements DomainEventHandler
{

    public function __construct(
        private IProjectReportsRepository $projectReportsRepository,
        private OrderTrackable $orderTracker
    ) {
    }

    public function handle(AbstractEvent $domainEvent): void
    {
        $projectReports = $domainEvent->entity;
        $payload = $projectReports->payload()->asArray()['event_data'];
        $this->projectReportsRepository->create($projectReports);

        $orderInfo = $this->orderTracker->order->payload()->asArray()['event_data'];
        $affiliateTrackable = isset($orderInfo['partners']['linkmink']['referral_data']);

        $projectReports->payload()->addAList([
            'event_data' =>
            [
                'customer' =>
                [
                    'property' =>
                    [
                        'address' => $orderInfo['customer']['property']['address'],
                        'city' => $orderInfo['customer']['property']['city'],
                        'state' => $orderInfo['customer']['property']['state'],
                        'zipcode' => $orderInfo['customer']['property']['zipcode'],
                        'country' => $orderInfo['customer']['personal_information']['country_code'],
                    ],
                    'payment' => [
                        'customer_token' => $orderInfo['customer']['payment']['customer_token'],
                    ],
                    'personal_information' => $orderInfo['customer']['personal_information'],
                    'location' => $orderInfo['customer']['location']
                ],
                'affiliate_trackable' => $affiliateTrackable ? $orderInfo['partners']['linkmink']['referral_data'] : false,
                'project' => isset($payload['project']) ? array_merge($orderInfo['project'], $payload['project']) : $orderInfo['project'],
            ]
        ]);
    }

    public function isSubscribedTo(AbstractEvent $domainEvent): bool
    {
        return $domainEvent instanceof ProjectReportsStatusChanged;
    }
}
