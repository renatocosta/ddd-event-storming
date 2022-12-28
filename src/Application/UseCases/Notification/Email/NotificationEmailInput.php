<?php

namespace Application\UseCases\Notification\Email;

final class NotificationEmailInput
{

    public function __construct(public string $recipient, public string $subject, public array $body = [])
    {
    }
}
