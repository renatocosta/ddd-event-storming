<?php

namespace Application\UseCases\Customer\Create;

use Common\ValueObjects\Geography\Country;
use Common\ValueObjects\Misc\Email;
use Common\ValueObjects\Misc\Mobile;
use Common\ValueObjects\Person\Name;
use Domain\Model\Customer\Customer;

final class CreateCustomerUseCase implements ICreateCustomerUseCase
{

    public function __construct(public Customer $customer)
    {
    }

    public function execute(CreateCustomerInput $input): void
    {
        $this->customer
            ->from(new Name($input->name), new Mobile($input->mobile), new Email($input->email), new Country($input->countryCode))
            ->create();
    }
}
