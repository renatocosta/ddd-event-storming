<?php

namespace Domain\Model\Order\State;

use Domain\Model\Order\OrderStatus;

class OrderCreatedState extends OrderState
{

    protected int $fromStatus = OrderStatus::CREATED;

    public function confirm(): OrderStatus
    {
        return new OrderStatus(OrderStatus::CONFIRMED);
    }
}
