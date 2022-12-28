<?php

namespace Application\EventHandlers\Notification;

use Common\Application\Event\AbstractEvent;
use Common\Application\Event\DomainEventHandler;
use Common\ValueObjects\Misc\SmsText;
use Domain\Model\Notification\Events\NotificationSmsNotified;
use Twilio\Rest\Client;

final class NotificationSmsNotifiedEventHandler implements DomainEventHandler
{

    public function __construct(private Client $twilio)
    {
    }

    public function handle(AbstractEvent $domainEvent): void
    {

        $notification = $domainEvent->entity;
        $body = new SmsText($notification->body()['text']);

        $this->twilio->messages
            ->create(
                '+' . $notification->recipient(),
                ["body" => (string) $body, "from" => env('TWILIO_SENDER')]
            );
    }

    public function isSubscribedTo(AbstractEvent $domainEvent): bool
    {
        return $domainEvent instanceof NotificationSmsNotified;
    }
}
