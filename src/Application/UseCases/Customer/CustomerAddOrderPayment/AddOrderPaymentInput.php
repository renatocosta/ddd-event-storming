<?php

namespace Application\UseCases\Customer\CustomerAddOrderPayment;

final class AddOrderPaymentInput
{

    public function __construct(public string $orderId, public string $paymentMethodToken, public string $cardNumberLast4, public string $customerToken)
    {
    }
}
