<?php

namespace Domain\Model\Order\State;

use Domain\Model\Order\OrderStatus;
use Domain\Model\Order\UnableToHandleOrders;
use Exception;

interface OrderStateable
{

    /**
     * @throws UnableToHandleOrders
     * @throws Exception
     * @return OrderStatus
     */
    public function uncreate(): OrderStatus;

    /**
     * @throws UnableToHandleOrders
     * @throws Exception
     * @return OrderStatus
     */
    public function create(): OrderStatus;

    /**
     * @throws UnableToHandleOrders
     * @throws Exception
     * @return OrderStatus
     */
    public function confirm(): OrderStatus;

    /**
     * @throws UnableToHandleOrders
     * @throws Exception
     * @return OrderStatus
     */
    public function update(): OrderStatus;

}
