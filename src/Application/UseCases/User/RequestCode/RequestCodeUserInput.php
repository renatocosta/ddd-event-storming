<?php

namespace Application\UseCases\User\RequestCode;

final class RequestCodeUserInput
{

    public function __construct(public string $orderId)
    {
    }
}
