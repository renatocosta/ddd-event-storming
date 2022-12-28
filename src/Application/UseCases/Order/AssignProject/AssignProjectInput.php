<?php

namespace Application\UseCases\Order\AssignProject;

final class AssignProjectInput
{

    public function __construct(public string $orderId, public int $projectId)
    {
    }
}
