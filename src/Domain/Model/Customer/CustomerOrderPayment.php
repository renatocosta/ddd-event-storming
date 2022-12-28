<?php

namespace Domain\Model\Customer;

use Assert\Assert;
use Assert\AssertionFailedException;
use Common\ValueObjects\Entity;
use Common\ValueObjects\Identity\Identified;

final class CustomerOrderPayment extends Entity
{

    public function __construct(public string $paymentMethodToken, public string $cardNumberLast4, public string $customerToken, public Identified $orderId)
    {

        try {
            Assert::that($paymentMethodToken)->notEmpty()->maxLength(100);
            Assert::that($cardNumberLast4)->notEmpty()->length(4);
            Assert::that($customerToken)->notEmpty()->maxLength(100);
        } catch (AssertionFailedException $e) {
            throw UnableToHandleCustomers::dueTo($e->getMessage());
        }
    }

    public function paymentMethodToken(): string
    {
        return $this->paymentMethodToken;
    }

    public function cardNumberLast4(): string
    {
        return $this->cardNumberLast4;
    }

    public function customerToken(): string
    {
        return $this->customerToken;
    }

    public function orderId(): Identified
    {
        return $this->orderId;
    }
}
