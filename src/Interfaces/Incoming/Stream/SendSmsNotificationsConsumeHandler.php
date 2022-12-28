<?php

namespace Interfaces\Incoming\Stream;

use Application\UseCases\Notification\Sms\INotificationSmsNotifyUseCase;
use Interop\Queue\Context;
use Interop\Queue\Message;
use Ddd\BnbEnqueueClient\Contracts\EventHandlerInterface;
use Application\UseCases\Notification\Sms\NotificationSmsInput;

class SendSmsNotificationsConsumeHandler implements EventHandlerInterface
{

    public function handle(Message $message, Context $context): void
    {
        $notificationSmsNotifyUseCase = app(INotificationSmsNotifyUseCase::class);

        $payload = json_decode($message->getBody(), true);
        $notificationSmsNotifyUseCase->execute(new NotificationSmsInput($payload['recipient'], $payload['body']));
    }
}
