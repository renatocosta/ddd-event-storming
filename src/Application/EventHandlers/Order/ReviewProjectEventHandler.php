<?php

namespace Application\EventHandlers\Order;

use Common\Application\Event\AbstractEvent;
use Common\Application\Event\DomainEventHandler;
use Domain\Model\Order\Events\ProjectReviewed;
use Domain\Model\Order\IOrderRepository;

final class ReviewProjectEventHandler implements DomainEventHandler
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
        return $domainEvent instanceof ProjectReviewed;
    }
}
