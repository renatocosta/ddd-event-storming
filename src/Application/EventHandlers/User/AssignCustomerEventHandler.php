<?php

namespace Application\EventHandlers\User;

use Common\Application\Event\AbstractEvent;
use Common\Application\Event\DomainEventHandler;
use Domain\Model\User\Events\CustomerAssignedToUser;
use Domain\Model\User\IUserRepository;

final class AssignCustomerEventHandler implements DomainEventHandler
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
        return $domainEvent instanceof CustomerAssignedToUser;
    }
}
