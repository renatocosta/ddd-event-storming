<?php

namespace Application\UseCases\Customer\Create;

interface ICreateCustomerUseCase
{

    public function execute(CreateCustomerInput $input): void;
}
