<?php

namespace Application\EventHandlers\User;

use Application\UseCases\Notification\Sms\INotificationSmsPublishUseCase;
use Application\UseCases\Notification\Sms\NotificationSmsInput;
use Application\UseCases\User\Identify\IdentifyUserInput;
use Application\UseCases\User\Identify\IdentifyUserUseCase;
use Common\Application\Event\AbstractEvent;
use Common\Application\Event\DomainEventHandler;
use Domain\Model\Notification\NotificationMessage;
use Domain\Model\User\Events\UserVerificationCodeRequested;

final class UserVerificationCodeRequestedEventHandler implements DomainEventHandler
{

    public function __construct(private IdentifyUserUseCase $identifyUserUseCase, private INotificationSmsPublishUseCase $notificationSmsPublishUseCase)
    {
    }

    public function handle(AbstractEvent $domainEvent): void
    {
        $user = $domainEvent->entity;
        $this->identifyUserUseCase->execute(new IdentifyUserInput($user->name(), $user->email(), $user->mobile(), $user->orderId(), $user->getCustomerId()));
        $verificationCode = $user->password()->asRaw();
        $text = sprintf(NotificationMessage::PHONE_NUMBER_VERIFICATION, $verificationCode);
        $this->notificationSmsPublishUseCase->execute(new NotificationSmsInput($user->mobile(), ['text' => $text]));
    }

    public function isSubscribedTo(AbstractEvent $domainEvent): bool
    {
        return $domainEvent instanceof UserVerificationCodeRequested;
    }
}
