<?php

namespace Application\UseCases\Notification\Email;

use Domain\Model\Notification\Events\NotificationEmailNotified;
use Domain\Model\Notification\Notification;

final class NotificationEmailNotifyUseCase implements INotificationEmailNotifyUseCase
{

    public function __construct(public Notification $notification)
    {
    }

    public function execute(NotificationEmailInput $input): void
    {
        $this->notification->of($input->body, $input->recipient, $input->subject);
        $this->notification->notify(new NotificationEmailNotified($this->notification));
    }
}
