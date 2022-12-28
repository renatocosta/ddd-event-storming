<?php

namespace Application\UseCases\Customer\CustomerAddOrderLocation;

interface IAddOrderLocationUseCase
{

    public function execute(AddOrderLocationInput $input): void;
}
