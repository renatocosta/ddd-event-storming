<?php

namespace Domain\Model\Notification;

use Common\Application\Event\Eventable;
use Common\ValueObjects\AggregateRoot;
use Domain\Model\Notification\Events\NotificationEmailNotified;
use Domain\Model\Notification\Events\NotificationEmailPublished;
use Domain\Model\Notification\Events\NotificationSmsNotified;
use Domain\Model\Notification\Events\NotificationSmsPublished;

final class NotificationEntity extends AggregateRoot implements Notification
{

    private array $body;

    private string $sender;

    private string $subject = '';

    private string $recipient;

    public function asSender(string $who): self
    {
        $this->sender = $who;
        return $this;
    }

    public function asRecipient(string $who): self
    {
        $this->recipient = $who;
        return $this;
    }

    public function body(): array
    {
        return $this->body;
    }

    public function subject(): string
    {
        return $this->subject;
    }

    public function sender(): string
    {
        return $this->sender;
    }

    public function recipient(): string
    {
        return $this->recipient;
    }

    public function publish(Eventable $event): void
    {
        if (!$event instanceof NotificationSmsPublished && !$event instanceof NotificationEmailPublished) {
            throw UnableToHandleNotification::dueTo(UnableToHandleNotification::MISMATCH_EVENT, $event->getEventName());
        }
        $this->raise($event);
    }

    public function notify(Eventable $event): void
    {
        if (!$event instanceof NotificationSmsNotified && !$event instanceof NotificationEmailNotified) {
            throw UnableToHandleNotification::dueTo(UnableToHandleNotification::MISMATCH_EVENT, $event->getEventName());
        }
        $this->raise($event);
    }

    public function of(array $body, string $recipient, string $subject = '', string $sender = null): self
    {
        $this->body = $body;
        $this->recipient = $recipient;
        $this->subject = $subject;
        return $this;
    }
}
