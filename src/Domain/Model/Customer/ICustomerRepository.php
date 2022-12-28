<?php

namespace Domain\Model\Customer;
interface ICustomerRepository
{

    public function getById(int $id): Customer;

    public function createWithOrderLocation(object $entity): void;

    public function createWithOrderPayment(object $entity): void;

    public function create(object $entity): void;

    public function update(object $entity): void;

}
