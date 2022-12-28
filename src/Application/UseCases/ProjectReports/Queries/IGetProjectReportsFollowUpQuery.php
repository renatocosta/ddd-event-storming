<?php

namespace Application\UseCases\ProjectReports\Queries;

use Domain\Model\ProjectReports\ProjectReportsStatus;

interface IGetProjectReportsFollowUpQuery
{

    /**
     *
     * @param string
     * @param array<int, ProjectReportsStatus>
     */
    public function execute(string $orderNumber, array $statuses = []): array;
}
