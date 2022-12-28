<?php

namespace Application\UseCases\Customer\CustomerAddOrderLocation;

final class AddOrderLocationInput
{

    public function __construct(public string $timezone, public string $orderId, public int $customerId)
    {
    }
}
