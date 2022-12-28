<?php

namespace Application\UseCases\Order\Create;

final class CreateOrderInput
{

    public function __construct(public array $payload, public int $customerId, public int $propertyId)
    {
    }
}
