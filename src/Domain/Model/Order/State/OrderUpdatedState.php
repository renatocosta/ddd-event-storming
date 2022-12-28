<?php

namespace Domain\Model\Order\State;

use Domain\Model\Order\OrderStatus;

class OrderUpdatedState extends OrderState
{

    protected int $fromStatus = OrderStatus::UPDATED;

    public function update(): OrderStatus
    {
        return new OrderStatus(OrderStatus::UPDATED);
    }
}
