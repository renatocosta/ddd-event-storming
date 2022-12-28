<?php

namespace Infrastructure\Repositories;

use BadMethodCallException;
use Common\ValueObjects\Identity\Identified;
use Domain\Model\User\IUserRepository;
use Domain\Model\User\User;
use Infrastructure\Framework\Entities\UserModel;

final class UserRepositoryFake implements IUserRepository
{

    public function get(string $mobile): object|null
    {
        throw new BadMethodCallException('Not implemented yet');
    }

    public function getAll(array $filter = []): array
    {
        throw new BadMethodCallException('Not implemented yet');
    }

    public function getByEmailAndPassword(string $email, string $password): UserModel
    {
        throw new BadMethodCallException('Not implemented yet');
    }

    public function getById(Identified $identifier): UserModel
    {
        throw new BadMethodCallException('Not implemented yet');
    }

    public function getByCustomerAndUserId(int $customerId, string $userId): User
    {
        throw new BadMethodCallException('Not implemented yet');
    }

    public function getWithOrder(string $orderId): object|null
    {
        throw new BadMethodCallException('Not implemented yet');
    }

    public function getWithOrderNumber(string $orderNumber): object|null
    {
        throw new BadMethodCallException('Not implemented yet');
    }

    public function update(object $entity): void
    {
        throw new BadMethodCallException('Not implemented yet');
    }

    public function create(object $user): UserModel
    {
        throw new BadMethodCallException('Not implemented yet');
    }

    public function getWithOrderAndMobile(string $orderNumber, string $mobile): object|null
    {
        throw new BadMethodCallException('Not implemented yet');
    }

    public function getWithOrderAndEmail(string $orderNumber, string $email): object|null
    {
        throw new BadMethodCallException('Not implemented yet');
    }

    public function getByCustomer(int $customerId): User
    {
        throw new BadMethodCallException('Not implemented yet');   
    }
}
