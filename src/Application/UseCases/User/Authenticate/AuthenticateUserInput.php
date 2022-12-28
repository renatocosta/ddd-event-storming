<?php

namespace Application\UseCases\User\Authenticate;

final class AuthenticateUserInput
{

    public function __construct(public string $mobile, public string $smsVerificationCode, public string $orderId, public string $orderNumber)
    {
    }
}
