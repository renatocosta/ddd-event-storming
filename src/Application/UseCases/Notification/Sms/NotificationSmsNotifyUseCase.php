<?php

namespace Application\UseCases\Notification\Sms;

use Domain\Model\Notification\Events\NotificationSmsNotified;
use Domain\Model\Notification\Notification;

final class NotificationSmsNotifyUseCase implements INotificationSmsNotifyUseCase
{

    public function __construct(public Notification $notification)
    {
    }

    public function execute(NotificationSmsInput $input): void
    {
        $this->notification->of($input->body, $input->recipient);
        $this->notification->notify(new NotificationSmsNotified($this->notification));
    }
}
