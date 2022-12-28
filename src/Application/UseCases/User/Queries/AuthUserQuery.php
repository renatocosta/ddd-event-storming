<?php

namespace Application\UseCases\User\Queries;

use Domain\Model\User\IUserRepository;
use Domain\Model\User\UserAbilities;

final class AuthUserQuery implements IAuthUserQuery
{

    public function __construct(private IUserRepository $userRepository)
    {
    }

    public function execute(string $email, string $password): string
    {
        $user = $this->userRepository->getByEmailAndPassword($email, $password);
        $user->tokens()->delete();
        return $user->createToken('token_auth_admin', (new UserAbilities(
            UserAbilities::MANAGE_GLOBAL,
            UserAbilities::MANAGE_ORDER,
            UserAbilities::MANAGE_PROJECT,
            UserAbilities::MANAGE_USER
        ))->abilities())->plainTextToken;
    }
}
