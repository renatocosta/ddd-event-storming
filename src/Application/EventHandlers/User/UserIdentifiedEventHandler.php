<?php

namespace Application\EventHandlers\User;

use Common\Application\Event\AbstractEvent;
use Common\Application\Event\DomainEventHandler;
use Common\ValueObjects\Identity\Guid;
use Domain\Model\User\Events\UserIdentified;
use Domain\Model\User\IUserRepository;

final class UserIdentifiedEventHandler implements DomainEventHandler
{

    public function __construct(private IUserRepository $userRepository)
    {
    }

    public function handle(AbstractEvent $domainEvent): void
    {
        $user = $domainEvent->entity;
        $userCreated = $this->userRepository->create($user);
        $user->setIdentifier(Guid::from($userCreated->id));
        $user->setUserIdentified($userCreated);
    }

    public function isSubscribedTo(AbstractEvent $domainEvent): bool
    {
        return $domainEvent instanceof UserIdentified;
    }
}
