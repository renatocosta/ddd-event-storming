<?php

namespace Application\UseCases\Order\Queries;

use Domain\Model\Order\Order;
use Domain\Services\IPropertyAddressInUse;

final class GetAddressAvailabilityQuery implements IGetAddressAvailabilityQuery
{

    public function __construct(private IPropertyAddressInUse $propertyAddressInUse, public Order $order)
    {
    }

    public function execute(string $address, string $unitNumber = null): bool
    {
        return $this->propertyAddressInUse->match($address, $unitNumber);
    }
}
