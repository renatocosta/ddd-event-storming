<?php

namespace Application\UseCases\Order\Confirm;

final class ConfirmOrderInput
{

    public function __construct(public string $id, public int $smsVerificationCode)
    {
    }
}
