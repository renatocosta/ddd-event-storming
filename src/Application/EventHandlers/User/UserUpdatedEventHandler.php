<?php

namespace Application\EventHandlers\User;

use Common\Application\Event\AbstractEvent;
use Common\Application\Event\DomainEventHandler;
use Domain\Model\User\Events\UserUpdated;
use Domain\Model\User\IUserRepository;

final class UserUpdatedEventHandler implements DomainEventHandler
{

    public function __construct(private IUserRepository $userRepository)
    {
    }

    public function handle(AbstractEvent $domainEvent): void
    {
        $user = $domainEvent->entity;
        $this->userRepository->update($user);
    }

    public function isSubscribedTo(AbstractEvent $domainEvent): bool
    {
        return $domainEvent instanceof UserUpdated;
    }
}
