<?php

namespace Application\UseCases\User\Queries;

use Domain\Model\User\User;

interface IGetUserCustomerQuery
{
    public function execute(int $customerId): User;

    public function getUser(): User;
}
