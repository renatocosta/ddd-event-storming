<?php

namespace Application\UseCases\User\PaymentMethod;

final class PaymentMethodInput
{

    public function __construct(public ?int $customerId, public string $paymentMethodToken, public string $userId)
    {
    }
}
