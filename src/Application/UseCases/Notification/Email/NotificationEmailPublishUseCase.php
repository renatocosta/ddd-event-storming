<?php

namespace Application\UseCases\Notification\Email;

use Domain\Model\Notification\Events\NotificationEmailPublished;
use Domain\Model\Notification\Notification;

final class NotificationEmailPublishUseCase implements INotificationEmailPublishUseCase
{

    public function __construct(public Notification $notification)
    {
    }

    public function execute(NotificationEmailInput $input): void
    {
        $this->notification->of($input->body, $input->recipient, $input->subject);
        $this->notification->publish(new NotificationEmailPublished($this->notification));
    }
}
