<?php

namespace Application\UseCases\Customer\CustomerAddOrderPayment;

use Common\ValueObjects\Identity\Guid;
use Domain\Model\Customer\Customer;
use Domain\Model\Customer\CustomerOrderPayment;

final class AddOrderPaymentUseCase implements IAddOrderPaymentUseCase
{

    public function __construct(public Customer $customer)
    {
    }

    public function execute(AddOrderPaymentInput $input): void
    {
        $this->customer
            ->fromExisting($this->customer->getId(), $this->customer->name(), $this->customer->mobile(), $this->customer->email(), $this->customer->country())
            ->addOrderPayment(new CustomerOrderPayment($input->paymentMethodToken, $input->cardNumberLast4, $input->customerToken, Guid::from($input->orderId)));
    }
}
