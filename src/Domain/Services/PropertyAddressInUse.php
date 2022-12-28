<?php

namespace Domain\Services;

use Common\Specification\CompositeSpecification;
use Common\Specification\Specification;

final class PropertyAddressInUse implements IPropertyAddressInUse
{

    public function __construct(private CompositeSpecification $propertyAddressPreviouslyAssociated, private Specification $followUpInProgress)
    {
    }

    public function match(string $address, string $unitNumber = null): bool
    {
        if (
            $this->propertyAddressPreviouslyAssociated->not()->isSatisfiedBy(['address' => $address, 'unit_number' => $unitNumber]) ||
            $this->followUpInProgress->not()->isSatisfiedBy(['order_id' => $this->propertyAddressPreviouslyAssociated->order->getIdentifier()->id, 'address' => $address, 'unit_number' => $unitNumber])
        ) {
            return false;
        }

        return true;
    }
}
