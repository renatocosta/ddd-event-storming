<?php

namespace Domain\Model\Order\State;

use Domain\Model\Order\Order;
use Domain\Model\Order\OrderStatus;
use Domain\Model\Order\UnableToHandleOrders;

final class OrderTransition
{

    private OrderState $state;

    public function __construct(private Order $order)
    {
        $this->state = match ($order->getStatus()->getId()) {
            OrderStatus::UNCREATED => new OrderUncreatedState(),
            OrderStatus::CREATED => new OrderCreatedState(),
            OrderStatus::CONFIRMED => new OrderConfirmedState(),
            OrderStatus::UPDATED => new OrderUpdatedState(),
            default => throw UnableToHandleOrders::dueTo(UnableToHandleOrders::UNKNOWN_ORDER_STATE_ON_CONFIRMATION, $order->getIdentifier()->id),
        };
    }

    public function nextState(): OrderState
    {
        return $this->state;
    }
}
