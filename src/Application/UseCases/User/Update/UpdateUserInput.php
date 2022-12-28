<?php

namespace Application\UseCases\User\Update;

final class UpdateUserInput
{

    public function __construct(public string $orderId, public string $mobile)
    {
    }
}
