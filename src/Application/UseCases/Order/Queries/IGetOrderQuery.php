<?php

namespace Application\UseCases\Order\Queries;

use Domain\Model\Order\Order;

interface IGetOrderQuery
{

    public function execute(string $orderNumber): Order;
}
