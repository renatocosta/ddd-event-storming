<?php

namespace Application\UseCases\User\Queries;

use Domain\Model\User\IUserRepository;
use Domain\Model\User\User;

final class GetUserCustomerQuery implements IGetUserCustomerQuery
{

    public User $user;

    public function __construct(private IUserRepository $userRepository)
    {
    }

    public function execute(int $customerId): User
    {
        $this->user = $this->userRepository->getByCustomer($customerId);
        return $this->user;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
