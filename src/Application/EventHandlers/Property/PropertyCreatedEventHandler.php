<?php

namespace Application\EventHandlers\Property;

use Common\Application\Event\AbstractEvent;
use Common\Application\Event\DomainEventHandler;
use Domain\Model\Property\Events\PropertyCreated;
use Domain\Model\Property\IPropertyRepository;

final class PropertyCreatedEventHandler implements DomainEventHandler
{

    public function __construct(
        private IPropertyRepository $propertyRepository
    ) {
    }

    public function handle(AbstractEvent $domainEvent): void
    {
        $this->propertyRepository->create($domainEvent->entity);
    }

    public function isSubscribedTo(AbstractEvent $domainEvent): bool
    {
        return $domainEvent instanceof PropertyCreated;
    }
}
