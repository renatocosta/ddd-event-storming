<?php

namespace Application\UseCases\Order\AddMoreCleaners;

final class AddMoreCleanersInput
{

    public function __construct(public string $orderId, public array $cleaners = [])
    {
    }
}
