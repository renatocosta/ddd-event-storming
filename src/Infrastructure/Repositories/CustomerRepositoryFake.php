<?php

namespace Infrastructure\Repositories;

use BadMethodCallException;
use Domain\Model\Customer\Customer;
use Domain\Model\Customer\ICustomerRepository;

final class CustomerRepositoryFake implements ICustomerRepository
{

    public function getById(int $id): Customer
    {
        throw new BadMethodCallException('Not implemented yet');
    }

    public function create(object $item): void
    {
        throw new BadMethodCallException('Not implemented yet');
    }

    public function update(object $customer): void
    {
        throw new BadMethodCallException('Not implemented yet');
    }

    public function createWithOrderLocation(object $entity): void
    {
        throw new BadMethodCallException('Not implemented yet');
    }

    public function createWithOrderPayment(object $entity): void
    {
        throw new BadMethodCallException('Not implemented yet');
    }
}
