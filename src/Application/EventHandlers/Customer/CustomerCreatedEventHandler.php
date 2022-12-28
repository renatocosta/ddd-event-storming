<?php

namespace Application\EventHandlers\Customer;

use Common\Application\Event\AbstractEvent;
use Common\Application\Event\DomainEventHandler;
use Domain\Model\Customer\Events\CustomerCreated;
use Domain\Model\Customer\ICustomerRepository;

final class CustomerCreatedEventHandler implements DomainEventHandler
{

    public function __construct(
        private ICustomerRepository $customerRepository
    ) {
    }

    public function handle(AbstractEvent $domainEvent): void
    {
        $this->customerRepository->create($domainEvent->entity);
    }

    public function isSubscribedTo(AbstractEvent $domainEvent): bool
    {
        return $domainEvent instanceof CustomerCreated;
    }
}
