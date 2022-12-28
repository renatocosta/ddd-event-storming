<?php

namespace Application\UseCases\ProjectReports\Queries;

use Domain\Model\ProjectReports\ProjectReports;
use Domain\Model\ProjectReports\ProjectReportsStatus;

interface IGetProjectReportsShareQuery
{
    public function execute(string $orderId, ProjectReportsStatus $status): ProjectReports;
}
