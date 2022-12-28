<?php

namespace Application\UseCases\Customer\CustomerAddOrderPayment;

interface IAddOrderPaymentUseCase
{

    public function execute(AddOrderPaymentInput $input): void;
}
