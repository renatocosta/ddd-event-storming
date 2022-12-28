<?php

namespace Application\UseCases\Order\SendTip;

final class SendTipInput
{

    public function __construct(public int $projectId, public float $amount, public string $currency, public int $customerId)
    {
    }
}
