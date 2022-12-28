<?php

namespace Application\UseCases\Customer\Create;

final class CreateCustomerInput
{

    public function __construct(public string $name, public string $mobile, public string $email, public string $countryCode)
    {
    }
}
