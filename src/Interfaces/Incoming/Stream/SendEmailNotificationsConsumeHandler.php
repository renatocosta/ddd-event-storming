<?php

namespace Interfaces\Incoming\Stream;

use Application\UseCases\Notification\Email\INotificationEmailNotifyUseCase;
use Application\UseCases\Notification\Email\NotificationEmailInput;
use Interop\Queue\Context;
use Interop\Queue\Message;
use Ddd\BnbEnqueueClient\Contracts\EventHandlerInterface;

class SendEmailNotificationsConsumeHandler implements EventHandlerInterface
{

    public function handle(Message $message, Context $context): void
    {
        $notificationEmailNotifyUseCase = app(INotificationEmailNotifyUseCase::class);

        $payload = json_decode($message->getBody(), true);
        $notificationEmailNotifyUseCase->execute(new NotificationEmailInput($payload['recipient'], $payload['subject'], $payload['body']));
    }
}
