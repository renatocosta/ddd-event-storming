<?php

namespace Application\UseCases\Customer\CustomerAddOrderLocation;

use Common\ValueObjects\Geography\Timezone;
use Common\ValueObjects\Identity\Guid;
use Domain\Model\Customer\Customer;
use Domain\Model\Customer\CustomerOrderLocation;

final class AddOrderLocationUseCase implements IAddOrderLocationUseCase
{

    public function __construct(public Customer $customer)
    {
    }

    public function execute(AddOrderLocationInput $input): void
    {
        $this->customer
            ->fromExisting($this->customer->getId(), $this->customer->name(), $this->customer->mobile(), $this->customer->email(), $this->customer->country())
            ->addOrderLocation(new CustomerOrderLocation(new Timezone($input->timezone), Guid::from($input->orderId)));
    }
}
