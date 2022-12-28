<?php

namespace Domain\Model\Notification;

use Common\Application\Event\Eventable;

interface Notification
{

    public function asSender(string $who): self;

    public function asRecipient(string $who): self;

    public function body(): array;

    public function sender(): string;

    public function recipient(): string;

    public function subject(): string;

    public function publish(Eventable $event): void;

    public function notify(Eventable $event): void;

    public function of(array $body, string $recipient, string $subject = '', string $sender = null): self;
}
