<?php

namespace Domain\Model\Order\State;

use Domain\Model\Order\OrderStatus;
use Domain\Model\Order\UnableToHandleOrders;

abstract class OrderState implements OrderStateable
{

    protected int $fromStatus;

    public function uncreate(): OrderStatus
    {
        throw UnableToHandleOrders::dueTo(UnableToHandleOrders::STATUS_TRANSITION_VIOLATION, OrderStatus::STATUS[$this->fromStatus], OrderStatus::STATUS[OrderStatus::UNCREATED]);
    }

    public function create(): OrderStatus
    {
        throw UnableToHandleOrders::dueTo(UnableToHandleOrders::STATUS_TRANSITION_VIOLATION, OrderStatus::STATUS[$this->fromStatus], OrderStatus::STATUS[OrderStatus::CREATED]);
    }

    public function confirm(): OrderStatus
    {
        throw UnableToHandleOrders::dueTo(UnableToHandleOrders::STATUS_TRANSITION_VIOLATION, OrderStatus::STATUS[$this->fromStatus], OrderStatus::STATUS[OrderStatus::CONFIRMED]);
    }

    public function update(): OrderStatus
    {
        throw UnableToHandleOrders::dueTo(UnableToHandleOrders::STATUS_TRANSITION_VIOLATION, OrderStatus::STATUS[$this->fromStatus], OrderStatus::STATUS[OrderStatus::UPDATED]);
    }
}
