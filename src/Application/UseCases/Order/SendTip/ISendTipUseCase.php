<?php

namespace Application\UseCases\Order\SendTip;

use Infrastructure\Proxy\ProxyedBnB;

interface ISendTipUseCase
{

    public function execute(SendTipInput $input): void;

    public function proxy(): ProxyedBnB;
}
