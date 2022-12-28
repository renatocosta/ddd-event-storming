<?php

namespace Application\EventHandlers\Order;

use Common\Application\Event\AbstractEvent;
use Common\Application\Event\DomainEventHandler;
use Domain\Model\Order\Events\OrderConfirmed;
use Domain\Model\Order\IOrderRepository;

final class OrderConfirmedEventHandler implements DomainEventHandler
{

    public function __construct(private IOrderRepository $orderRepository)
    {
    }

    public function handle(AbstractEvent $domainEvent): void
    {
        $order = $domainEvent->entity;
        $this->orderRepository->create($order);
    }

    public function isSubscribedTo(AbstractEvent $domainEvent): bool
    {
        return $domainEvent instanceof OrderConfirmed;
    }
}
