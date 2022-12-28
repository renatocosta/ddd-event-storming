<?php

namespace Application\EventHandlers\Notification;

use Common\Application\Event\AbstractEvent;
use Common\Application\Event\DomainEventHandler;
use Domain\Model\Notification\Events\NotificationSmsPublished;
use Ddd\BnbEnqueueClient\Facades\Producer;

final class NotificationSmsPublishedEventHandler implements DomainEventHandler
{

    public function __construct(private Producer $kafkaProducer)
    {
    }

    public function handle(AbstractEvent $domainEvent): void
    {
        $this->kafkaProducer::sendMessage("ddd.orders.smsnotifications", ['payload' => [
            "recipient" => $domainEvent->entity->recipient(),
            "subject" => $domainEvent->entity->subject(),
            "body" => $domainEvent->entity->body()
        ]]);
    }

    public function isSubscribedTo(AbstractEvent $domainEvent): bool
    {
        return $domainEvent instanceof NotificationSmsPublished;
    }
}
