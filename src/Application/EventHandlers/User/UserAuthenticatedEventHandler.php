<?php

namespace Application\EventHandlers\User;

use Common\Application\Event\AbstractEvent;
use Common\Application\Event\DomainEventHandler;
use Common\ValueObjects\Identity\SmsVerificationCode;
use Domain\Model\User\Events\UserAuthenticated;
use Domain\Model\User\IUserRepository;

final class UserAuthenticatedEventHandler implements DomainEventHandler
{

    public function __construct(private IUserRepository $userRepository, private SmsVerificationCode $smsVerificationCode)
    {
    }

    public function handle(AbstractEvent $domainEvent): void
    {
        $user = $domainEvent->entity;
        $user->andFinally();
    }

    public function isSubscribedTo(AbstractEvent $domainEvent): bool
    {
        return $domainEvent instanceof UserAuthenticated;
    }
}
