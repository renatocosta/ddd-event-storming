<?php

namespace Application\UseCases\User\Identify;

final class IdentifyUserInput
{

    public function __construct(public string $name, public string $email, public string $mobile, public string $orderId, public int $customerId)
    {
    }
}
