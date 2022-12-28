<?php

namespace Domain\Model\ProjectReports\State;

use Domain\Model\ProjectReports\ProjectReportsStatus;

class ProjectPaymentFailedState extends ProjectReportsState
{

    protected string $fromStatus = ProjectReportsStatus::PROJECT_PAYMENT_FAILED;

    public function markPaymentAsFailed(): ProjectReportsStatus
    {
        return new ProjectReportsStatus(ProjectReportsStatus::PROJECT_PAYMENT_FAILED);
    }

    public function reportProject(): ProjectReportsStatus
    {
        return new ProjectReportsStatus(ProjectReportsStatus::PROJECT_REPORTED);
    }
}
