<?php

namespace Application\UseCases\User\Update;

use Common\ValueObjects\Identity\Guid;
use Common\ValueObjects\Misc\Email;
use Common\ValueObjects\Misc\Mobile;
use Domain\Model\User\IUserRepository;
use Domain\Model\User\User;

final class UpdateUserUseCase implements IUpdateUserUseCase
{

    public function __construct(public User $user, private IUserRepository $userRepository)
    {
    }

    public function execute(UpdateUserInput $input): void
    {
        $existingUser = $this->userRepository->getWithOrder(Guid::from($input->orderId));
        $this->user->of(Guid::from($existingUser->id), $existingUser->name, new Mobile($input->mobile), new Email($existingUser->email));
        $this->user->update($existingUser);
    }
}
