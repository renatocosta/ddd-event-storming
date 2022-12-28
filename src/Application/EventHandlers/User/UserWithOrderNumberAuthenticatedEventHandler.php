<?php

namespace Application\EventHandlers\User;

use Common\Application\Event\AbstractEvent;
use Common\Application\Event\DomainEventHandler;
use Domain\Model\User\Events\UserWithOrderNumberAuthenticated;

final class UserWithOrderNumberAuthenticatedEventHandler implements DomainEventHandler
{

    public function handle(AbstractEvent $domainEvent): void
    {
        $user = $domainEvent->entity;
        $user->andFinally();
    }

    public function isSubscribedTo(AbstractEvent $domainEvent): bool
    {
        return $domainEvent instanceof UserWithOrderNumberAuthenticated;
    }
}
