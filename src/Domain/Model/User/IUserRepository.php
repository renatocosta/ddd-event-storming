<?php

namespace Domain\Model\User;

use Common\ValueObjects\Identity\Identified;
use Infrastructure\Framework\Entities\UserModel;

interface IUserRepository
{

    public function create(object $entity): UserModel;

    public function update(object $entity): void;

    public function get(string $mobile): object|null;

    public function getAll(array $filter = []): array;

    public function getByEmailAndPassword(string $email, string $password): UserModel;

    public function getById(Identified $identifier): UserModel;

    public function getWithOrderAndMobile(string $orderNumber, string $mobile): object|null;

    public function getWithOrderAndEmail(string $orderNumber, string $email): object|null;

    public function getWithOrderNumber(string $orderNumber): object|null;

    public function getWithOrder(string $orderId): object|null;

    public function getByCustomerAndUserId(int $customerId, string $userId): User;

    public function getByCustomer(int $customerId): User;

}
