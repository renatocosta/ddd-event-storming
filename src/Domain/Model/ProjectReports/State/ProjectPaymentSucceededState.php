<?php

namespace Domain\Model\ProjectReports\State;

use Domain\Model\ProjectReports\ProjectReportsStatus;

class ProjectPaymentSucceededState extends ProjectReportsState
{

    protected string $fromStatus = ProjectReportsStatus::PROJECT_PAYMENT_SUCCEEDED;

    public function reportProject(): ProjectReportsStatus
    {
        return new ProjectReportsStatus(ProjectReportsStatus::PROJECT_REPORTED);
    }
}
