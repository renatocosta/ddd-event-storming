<?php

namespace Application\UseCases\ProjectReports\Replicate;

final class ReplicateProjectReportsInput
{

    public function __construct(public string $orderId, public string $payload)
    {
    }
}
