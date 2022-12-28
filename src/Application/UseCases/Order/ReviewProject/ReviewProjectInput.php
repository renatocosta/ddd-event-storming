<?php

namespace Application\UseCases\Order\ReviewProject;

final class ReviewProjectInput
{

    public function __construct(public string $orderId)
    {
    }
}
