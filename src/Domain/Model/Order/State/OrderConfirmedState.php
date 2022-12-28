<?php

namespace Domain\Model\Order\State;

use Domain\Model\Order\OrderStatus;

class OrderConfirmedState extends OrderState
{

    protected int $fromStatus = OrderStatus::CONFIRMED;

    public function update(): OrderStatus
    {
        return new OrderStatus(OrderStatus::UPDATED);
    }
}
