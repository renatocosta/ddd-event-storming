<?php

namespace Domain\Model\ProjectReports\State;

use Domain\Model\ProjectReports\ProjectReportsStatus;

class ProjectReportedState extends ProjectReportsState
{

    protected string $fromStatus = ProjectReportsStatus::PROJECT_REPORTED;

    public function reportProject(): ProjectReportsStatus
    {
        return new ProjectReportsStatus(ProjectReportsStatus::PROJECT_REPORTED);
    }

    public function finishPayment(): ProjectReportsStatus
    {
        return new ProjectReportsStatus(ProjectReportsStatus::PROJECT_PAYMENT_SUCCEEDED);
    }

    public function markPaymentAsFailed(): ProjectReportsStatus
    {
        return new ProjectReportsStatus(ProjectReportsStatus::PROJECT_PAYMENT_FAILED);
    }

    public function cancelProject(): ProjectReportsStatus
    {
        return new ProjectReportsStatus(ProjectReportsStatus::PROJECT_CANCELLED);
    }
}
