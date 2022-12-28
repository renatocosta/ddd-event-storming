<?php

namespace Application\UseCases\User\PaymentMethod;

use Infrastructure\Proxy\ProxyedBnB;

interface IPaymentMethodUseCase
{

    public function execute(PaymentMethodInput $input): void;

    public function proxy(): ProxyedBnB;
}
