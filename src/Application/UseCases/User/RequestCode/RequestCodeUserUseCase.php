<?php

namespace Application\UseCases\User\RequestCode;

use Common\ValueObjects\Identity\Guid;
use Common\ValueObjects\Identity\Password;
use Common\ValueObjects\Identity\SmsVerificationCode;
use Common\ValueObjects\Misc\Email;
use Common\ValueObjects\Misc\Mobile;
use Domain\Model\User\IUserRepository;
use Domain\Model\User\User;

final class RequestCodeUserUseCase implements IRequestCodeUserUseCase
{

    public function __construct(public User $user, public IUserRepository $userRepository, private SmsVerificationCode $smsVerificationCode)
    {
    }

    public function execute(RequestCodeUserInput $input): void
    {
        $existingUser = $this->userRepository->getWithOrder(Guid::from($input->orderId));
        $this->user->withOrderId(Guid::from($existingUser->id), $existingUser->customer_id, $existingUser->name, new Mobile($existingUser->mobile), new Email($existingUser->email), new Password($this->smsVerificationCode->code(), SmsVerificationCode::LENGTH), $existingUser->customer->order->id);
        $this->user->requestCode();
    }
}
