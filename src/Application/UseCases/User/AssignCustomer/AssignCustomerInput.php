<?php

namespace Application\UseCases\User\AssignCustomer;

final class AssignCustomerInput
{

    public function __construct(public string $orderId, public int $customerId)
    {
    }
}
