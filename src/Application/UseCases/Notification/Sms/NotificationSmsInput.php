<?php

namespace Application\UseCases\Notification\Sms;

final class NotificationSmsInput
{

    public function __construct(public string $recipient, public array $body)
    {
    }
}
