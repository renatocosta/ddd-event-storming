<?php

namespace Application\UseCases\User\AuthenticateWithOrderNumber;

final class AuthenticateWithOrderNumberUserInput
{

    public function __construct(public string $emailMobile, public string $orderNumber)
    {
    }
}
