<?php

namespace Application\UseCases\ProjectReports\Queries;

use Domain\Model\ProjectReports\ProjectReports;

interface IGetProjectReportsQuery
{
    public function execute(string $orderNumber, int $status): ProjectReports;
}
