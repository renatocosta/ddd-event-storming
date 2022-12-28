<?php

namespace Domain\Model\Order\State;

use Domain\Model\Order\OrderStatus;

class OrderUncreatedState extends OrderState
{

    protected int $fromStatus = OrderStatus::UNCREATED;

    public function create(): OrderStatus
    {
        return new OrderStatus(OrderStatus::CREATED);
    }

}
