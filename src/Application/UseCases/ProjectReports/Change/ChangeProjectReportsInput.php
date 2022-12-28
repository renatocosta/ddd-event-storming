<?php

namespace Application\UseCases\ProjectReports\Change;

final class ChangeProjectReportsInput
{

    public function __construct(public string $orderId, public string $payload)
    {
    }
}
