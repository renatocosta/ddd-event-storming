<?php

namespace Application\EventHandlers\Customer;

use Common\Application\Event\AbstractEvent;
use Common\Application\Event\DomainEventHandler;
use Domain\Model\Customer\Events\CustomerUpdated;
use Domain\Model\Customer\ICustomerRepository;

final class CustomerUpdatedEventHandler implements DomainEventHandler
{

    public function __construct(
        private ICustomerRepository $customerRepository
    ) {
    }

    public function handle(AbstractEvent $domainEvent): void
    {
        $this->customerRepository->update($domainEvent->entity);
    }

    public function isSubscribedTo(AbstractEvent $domainEvent): bool
    {
        return $domainEvent instanceof CustomerUpdated;
    }
}
