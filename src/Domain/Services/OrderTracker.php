<?php

namespace Domain\Services;

use Common\ValueObjects\Identity\Identified;
use Domain\Model\Order\IOrderRepository;
use Domain\Model\Order\Order;

final class OrderTracker implements OrderTrackable
{

    public Order $order;

    public function __construct(private IOrderRepository $repository)
    {
    }

    public function fetchCurrent(Identified $orderId): Order
    {
        $this->order = $this->repository->getById($orderId);
        return $this->order;
    }
}
