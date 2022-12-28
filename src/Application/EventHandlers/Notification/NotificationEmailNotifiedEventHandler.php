<?php

namespace Application\EventHandlers\Notification;

use Common\Application\Event\AbstractEvent;
use Common\Application\Event\DomainEventHandler;
use Domain\Model\Notification\Events\NotificationEmailNotified;
use Illuminate\Support\Facades\Mail;

final class NotificationEmailNotifiedEventHandler implements DomainEventHandler
{

    public function handle(AbstractEvent $domainEvent): void
    {

        $body = $domainEvent->entity->body();
        $data = array_merge([
            'subject' => $domainEvent->entity->subject(),
            'email' => $domainEvent->entity->recipient()
        ], $body);

        Mail::send($body['view_template'], $data, function ($message) use ($data) {
            $message->to($data['email'])
                ->subject($data['subject']);
        });
    }

    public function isSubscribedTo(AbstractEvent $domainEvent): bool
    {
        return $domainEvent instanceof NotificationEmailNotified;
    }
}
