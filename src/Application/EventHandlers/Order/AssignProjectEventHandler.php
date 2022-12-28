<?php

namespace Application\EventHandlers\Order;

use Common\Application\Event\AbstractEvent;
use Common\Application\Event\DomainEventHandler;
use Domain\Model\Order\Events\ProjectAssignedToOrder;
use Domain\Model\Order\IOrderRepository;

final class AssignProjectEventHandler implements DomainEventHandler
{

    public function __construct(private IOrderRepository $orderRepository)
    {
    }

    public function handle(AbstractEvent $domainEvent): void
    {
        $order = $domainEvent->entity;
        $this->orderRepository->update($order);
    }

    public function isSubscribedTo(AbstractEvent $domainEvent): bool
    {
        return $domainEvent instanceof ProjectAssignedToOrder;
    }
}
