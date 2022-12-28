<?php

namespace Domain\Model\Order;

use Common\Repository\Repository;
use Common\ValueObjects\Identity\Identified;
use Common\ValueObjects\Misc\HumanCode;

interface IOrderRepository extends Repository
{

    public function getById(Identified $identifier): Order;

    public function getBy(HumanCode $orderNumber, array $filter = ['id', 'order_number', 'status', 'payload']): Order;

    public function getByProjectAndCustomerId(int $projectId, int $customerId, array $filter = ['id', 'order_number', 'status', 'payload']): Order;

    public function getByAddressAndNumber(string $address, string $number = null): Order;

}
