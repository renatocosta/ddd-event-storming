<?php

namespace Infrastructure\Repositories;

use BadMethodCallException;
use Common\ValueObjects\Identity\Identified;
use Common\ValueObjects\Misc\HumanCode;
use Domain\Model\Order\IOrderRepository;
use Domain\Model\Order\Order;
use Infrastructure\Framework\Providers\OrderFakerProvider;

final class OrderRepositoryFake implements IOrderRepository
{

    public function __construct(private OrderFakerProvider  $faker)
    {
    }

    public function getAll(array $filter = ['id', 'status'], string $id = null): array
    {
        throw new BadMethodCallException('Not implemented yet');
    }

    public function get(Identified $identifier, array $filter = ['id', 'status']): object
    {
        throw new BadMethodCallException('Not implemented yet');
    }

    public function getBy(HumanCode $orderNumber, array $filter = ['id', 'order_number', 'status', 'payload']): Order
    {
        throw new BadMethodCallException('Not implemented yet');
    }

    public function getById(Identified $identifier): Order
    {
        throw new BadMethodCallException('Not implemented yet');
    }

    public function getByAddressAndNumber(string $address, string $number = null): Order
    {
        throw new BadMethodCallException('Not implemented yet');
    }

    public function getByProjectAndCustomerId(int $projectId, int $customerId, array $filter = ['id', 'order_number', 'status', 'payload']): Order
    {
        throw new BadMethodCallException('Not implemented yet');
    }

    public function create(object $item): void
    {
    }

    public function update(object $item): void
    {
    }

    public function remove(object $entity): void
    {
        throw new BadMethodCallException('Not implemented yet');
    }
}
