<?php

namespace Application\UseCases\User\Identify;

use Common\ValueObjects\Identity\Guid;
use Common\ValueObjects\Identity\Password;
use Common\ValueObjects\Identity\SmsVerificationCode;
use Common\ValueObjects\Misc\Email;
use Common\ValueObjects\Misc\Mobile;
use Domain\Model\User\User;
use Ramsey\Uuid\Uuid;

final class IdentifyUserUseCase implements IIdentifyUserUseCase
{

    public function __construct(public User $user, private SmsVerificationCode $smsVerificationCode)
    {
    }

    public function execute(IdentifyUserInput $input): void
    {
        $this->user->withOrderId(Guid::from(Uuid::uuid4()), $input->customerId, $input->name, new Mobile($input->mobile), new Email($input->email), new Password($this->smsVerificationCode->code(), SmsVerificationCode::LENGTH), $input->orderId);
        $this->user->identify();
    }
}
