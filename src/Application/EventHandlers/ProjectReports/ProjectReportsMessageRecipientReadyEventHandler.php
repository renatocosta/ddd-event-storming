<?php

namespace Application\EventHandlers\ProjectReports;

use Common\Application\Event\AbstractEvent;
use Common\Application\Event\DomainEventHandler;
use Domain\Model\ProjectReports\Events\ProjectReportsStatusChanged;
use Domain\Model\ProjectReports\MessageReport\BodyPart;
use Domain\Model\ProjectReports\MessageReport\ChannelsPart;
use Domain\Model\ProjectReports\MessageReport\DestinationPart;
use Domain\Model\ProjectReports\MessageReport\MagicLinkPart;
use Domain\Model\ProjectReports\MessageReport\MessagePart;
use Domain\Model\ProjectReports\MessageReport\MessageRecipientable;
use Domain\Model\ProjectReports\MessageReport\StepPart;
use Domain\Services\OrderTrackable;

final class ProjectReportsMessageRecipientReadyEventHandler implements DomainEventHandler
{

    public function __construct(
        private MessageRecipientable $messageRecipient,
        private OrderTrackable $orderTracker
    ) {
    }

    public function handle(AbstractEvent $domainEvent): void
    {
        $projectReports = $domainEvent->entity;
        $payload = $projectReports->payload()->asArray();

        $this->messageRecipient->data->projectPayload = $payload;
        $this->messageRecipient->data->order = $this->orderTracker->order;
        $this->messageRecipient->data->orderPayload = $this->orderTracker->order->payload()->asArray();

        $messageParts = [new StepPart($payload['event_type'], $payload), new ChannelsPart($this->messageRecipient->data), new DestinationPart($this->messageRecipient->data), new MagicLinkPart($this->messageRecipient->data), new BodyPart($this->messageRecipient->data, auth()->user())];

        array_walk($messageParts, function (MessagePart $messagePart) {
            $messagePart->accept($this->messageRecipient);
        });
    }

    public function isSubscribedTo(AbstractEvent $domainEvent): bool
    {
        if (!$domainEvent instanceof ProjectReportsStatusChanged) return false;

        $data = $domainEvent->entity->payload()->asArray();
        return in_array($data['event_type'], array_keys(StepPart::STEPS));
    }
}
