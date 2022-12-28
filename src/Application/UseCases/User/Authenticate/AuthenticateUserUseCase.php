<?php

namespace Application\UseCases\User\Authenticate;

use Common\ValueObjects\Identity\SmsVerificationCode;
use Common\ValueObjects\Misc\Mobile;
use Domain\Model\User\IUserRepository;
use Domain\Model\User\User;

final class AuthenticateUserUseCase implements IAuthenticateUserUseCase
{

    public function __construct(public User $user, private IUserRepository $userRepository)
    {
    }

    public function execute(AuthenticateUserInput $input): void
    {
        $existingUser = $this->userRepository->get($input->mobile);
        $this->user->fromExisting(new Mobile($input->mobile), new SmsVerificationCode($input->smsVerificationCode), $input->orderId, $input->orderNumber);
        $this->user->authenticate($existingUser);
    }
}
