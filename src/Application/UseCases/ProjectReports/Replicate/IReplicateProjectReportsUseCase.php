<?php

namespace Application\UseCases\ProjectReports\Replicate;

interface IReplicateProjectReportsUseCase
{

    public function execute(ReplicateProjectReportsInput $input): void;
}
