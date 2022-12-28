<?php

namespace Domain\Model\Order\Specifications;

use Common\Specification\CompositeSpecification;
use Domain\Model\Order\IOrderRepository;
use Domain\Model\Order\Order;
use Domain\Model\Order\OrderEntityNotFound;

final class PropertyAddressPreviouslyAssociated extends CompositeSpecification
{

    public function __construct(private IOrderRepository $orderRepository, public Order $order)
    {
    }

    /**
     * @param array $params
     */
    public function isSatisfiedBy($params): bool
    {

        $order = $this->orderRepository->getByAddressAndNumber(
            $params['address'],
            $params['unit_number']
        );

        if ($order instanceof OrderEntityNotFound) {
            return false;
        }

        $this->order = $this->order->fromExisting($order->getIdentifier(), $order->getCustomerId(), $order->getPropertyId(), $order->getStatus(), $order->orderNumber(), $order->payload(), $order->mobileNumber(), $order->getProjectId());

        return true;
    }
}
