<?php

namespace Application\UseCases\User\AuthenticateWithOrderNumber;

use Common\ValueObjects\Misc\Mobile;
use Domain\Model\User\IUserRepository;
use Domain\Model\User\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class AuthenticateWithOrderNumberUserUseCase implements IAuthenticateWithOrderNumberUserUseCase
{

    public function __construct(public User $user, private IUserRepository $userRepository)
    {
    }

    public function execute(AuthenticateWithOrderNumberUserInput $input): void
    {

        if (filter_var($input->emailMobile, FILTER_VALIDATE_EMAIL)) {
            $existingUser = $this->userRepository->getWithOrderAndEmail($input->orderNumber, $input->emailMobile);
        } else {
            $existingUser = $this->userRepository->getWithOrderAndMobile($input->orderNumber, $input->emailMobile);
        }

        if (!$existingUser) throw new ModelNotFoundException();

        $this->user->from(new Mobile($existingUser->mobile), $input->orderNumber);
        $this->user->authenticateWithOrderNumber($existingUser);
    }
}
