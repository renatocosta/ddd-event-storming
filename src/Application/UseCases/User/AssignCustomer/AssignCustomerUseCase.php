<?php

namespace Application\UseCases\User\AssignCustomer;

use Domain\Model\User\IUserRepository;
use Domain\Model\User\User;

final class AssignCustomerUseCase implements IAssignCustomerUseCase
{

    public function __construct(public User $user, private IUserRepository $userRepository)
    {
    }

    public function execute(AssignCustomerInput $input): void
    {
        $existingUser = $this->userRepository->getByCustomer($input->customerId);
        $this->user->withCustomerId($existingUser->getIdentifier(), $existingUser->name(), $existingUser->mobile(), $existingUser->email(), $input->orderId, $input->customerId);
        $this->user->assignCustomer();
    }
}
