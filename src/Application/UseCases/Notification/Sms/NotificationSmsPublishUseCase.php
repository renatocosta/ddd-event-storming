<?php

namespace Application\UseCases\Notification\Sms;

use Domain\Model\Notification\Events\NotificationSmsPublished;
use Domain\Model\Notification\Notification;

final class NotificationSmsPublishUseCase implements INotificationSmsPublishUseCase
{

    public function __construct(public Notification $notification)
    {
    }

    public function execute(NotificationSmsInput $input): void
    {
        $this->notification->of($input->body, $input->recipient);
        $this->notification->publish(new NotificationSmsPublished($this->notification));
    }
}
