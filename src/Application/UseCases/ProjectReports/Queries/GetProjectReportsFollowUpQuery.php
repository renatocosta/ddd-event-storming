<?php

namespace Application\UseCases\ProjectReports\Queries;

use Common\ValueObjects\Misc\HumanCode;
use Domain\Model\ProjectReports\ProjectReportsStatus;
use Domain\Services\ProjectReportsMaterializable;

final class GetProjectReportsFollowUpQuery implements IGetProjectReportsFollowUpQuery
{

    public function __construct(private ProjectReportsMaterializable $projectFollowUp)
    {
    }

    public function execute(string $orderNumber, array $statuses = [ProjectReportsStatus::ORDER_ACCEPTED, ProjectReportsStatus::PROJECT_STARTED, ProjectReportsStatus::PROJECT_FINISHED, ProjectReportsStatus::PROJECT_CANCELLED, ProjectReportsStatus::PROJECT_CREDIT_CARD_AUTH_FAILED, ProjectReportsStatus::PROJECT_PAYMENT_FAILED]): array
    {
        return $this->projectFollowUp
            ->filterFrom(
                new HumanCode($orderNumber),
                ...array_map(fn ($projectStatus): ProjectReportsStatus => new ProjectReportsStatus($projectStatus), $statuses)
            )
            ->reconstituteFromEvent();
    }
}
