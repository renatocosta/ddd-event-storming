<?php

namespace Application\UseCases\Order\Queries;

use Common\ValueObjects\Misc\HumanCode;
use Domain\Model\Order\IOrderRepository;
use Domain\Model\Order\Order;
use Domain\Model\Order\UnableToHandleOrders;
use Exception;

final class GetOrderQuery implements IGetOrderQuery
{

    public function __construct(private Order $order, private IOrderRepository $orderRepository)
    {
    }

    public function execute(string $orderNumber): Order
    {

        try {
            $order = $this->order->from(new HumanCode($orderNumber));
        } catch (Exception $e) {
            throw UnableToHandleOrders::dueTo($e->getMessage());
        }
        return $this->orderRepository->getBy($order->orderNumber());
    }
}
